<?php

namespace App\Services;

use App\Models\ListingPhoto;
use App\Models\User;
use App\Repositories\Contracts\ListingPhotoRepositoryInterface;
use App\Repositories\Contracts\ListingRepositoryInterface;
use App\Repositories\Contracts\VegetableTypeRepositoryInterface;
use App\Services\Ai\Contracts\ProduceAnalyzer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ListingPhotoService
{
    public function __construct(
        private ListingRepositoryInterface $listings,
        private ListingPhotoRepositoryInterface $photos,
        private VegetableTypeRepositoryInterface $types,
        private ProduceAnalyzer $analyzer,
        private FreshnessService $freshness,
    ) {}

    /**
     * Hasil AI bisa datang dari aplikasi (TFLite offline, field predicted_*) atau dihitung server
     * lewat Gemini/Claude. Atribut sementara "ai_status": dari_aplikasi | server | dilewati | gagal.
     */
    public function upload(User $seller, int $listingId, UploadedFile $file, array $data): ListingPhoto
    {
        $listing = $this->listings->findOwnedOrFail($seller->id, $listingId);

        $path = $file->store('listing-photos', 'public');
        $isPrimary = (bool) ($data['is_primary'] ?? $listing->photos->isEmpty());

        $photo = $this->photos->create([
            'listing_id' => $listing->id,
            'path' => $path,
            'is_primary' => $isPrimary,
            'captured_offline' => (bool) ($data['captured_offline'] ?? false),
        ]);

        if ($isPrimary) {
            $this->photos->unsetPrimary($listing->id, $photo->id);
        }

        $status = 'dilewati';

        if (! empty($data['predicted_grade'])) {
            $photo->fill([
                'predicted_vegetable_type_id' => $data['predicted_vegetable_type_id'] ?? null,
                'type_confidence' => $data['type_confidence'] ?? null,
                'predicted_grade' => $data['predicted_grade'],
                'grade_confidence' => $data['grade_confidence'] ?? null,
                'is_sellable' => $data['predicted_grade'] !== 'C',
                'ai_model_version_id' => $this->photos->resolveModelVersion('tflite-app', 'TensorFlow Lite'),
                'synced_at' => now(),
            ])->save();
            $status = 'dari_aplikasi';
        } elseif ($data['use_ai'] ?? true) {
            $status = $this->analyzeOnServer($photo, $file) ? 'server' : 'gagal';
        }

        // Grade hanya diisi otomatis bila penjual belum menentukannya; penjual tetap bisa mengubah.
        if ($photo->predicted_grade && $listing->grade === null) {
            $listing->update(['grade' => $photo->predicted_grade]);
            $this->freshness->sync($listing);
        }

        $photo->setAttribute('ai_status', $status);

        return $photo->refresh()->setAttribute('ai_status', $status);
    }

    public function destroy(User $seller, int $listingId, int $photoId): void
    {
        $listing = $this->listings->findOwnedOrFail($seller->id, $listingId);
        $photo = $this->photos->findForListingOrFail($listing->id, $photoId);
        $wasPrimary = $photo->is_primary;

        Storage::disk('public')->delete($photo->path);
        $this->photos->delete($photo);

        if ($wasPrimary && ($next = $this->photos->firstOfListing($listing->id))) {
            $this->photos->update($next, ['is_primary' => true]);
        }
    }

    private function analyzeOnServer(ListingPhoto $photo, UploadedFile $file): bool
    {
        try {
            $known = $this->types->activeWithTiers()->map(fn ($t) => ['id' => $t->id, 'name' => $t->name])->all();
            $result = $this->analyzer->analyze($file->get(), $file->getMimeType() ?: 'image/jpeg', $known);

            $photo->update([
                'predicted_vegetable_type_id' => $result['vegetable_type_id'],
                'type_confidence' => $result['type_confidence'],
                'predicted_grade' => $result['grade'],
                'grade_confidence' => $result['grade_confidence'],
                'is_sellable' => $result['is_sellable'],
                'ai_model_version_id' => $this->photos->resolveModelVersion(
                    $this->analyzer->label(), $this->analyzer->framework()
                ),
                'synced_at' => now(),
            ]);

            return true;
        } catch (\Throwable $e) {
            // Foto tetap tersimpan; penjual bisa menentukan grade manual.
            Log::warning('Analisis foto AI gagal: ' . $e->getMessage());

            return false;
        }
    }
}