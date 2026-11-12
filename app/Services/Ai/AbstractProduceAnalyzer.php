<?php

namespace App\Services\Ai;

use App\Services\Ai\Contracts\ProduceAnalyzer;
use RuntimeException;

abstract class AbstractProduceAnalyzer implements ProduceAnalyzer
{
    protected function prompt(array $knownTypes): string
    {
        $daftar = collect($knownTypes)->map(fn ($t) => "{$t['id']} = {$t['name']}")->implode('; ');

        return <<<PROMPT
Anda penilai mutu hasil tani untuk pasar pangan. Lihat foto dan tentukan:
1. Jenis sayur/buah. Pilih SATU id dari daftar: {$daftar}. Jika tidak ada yang cocok, isi null.
2. Grade mutu:
   - A: segar, utuh, warna merata, tanpa cacat berarti.
   - B: layu ringan atau cacat kecil, masih layak jual.
   - C: busuk, layu parah, berjamur, atau rusak; tidak layak jual.
Jika foto bukan hasil tani atau terlalu gelap/buram, beri grade "C" dengan confidence rendah dan jelaskan di reason.
Balas HANYA dengan JSON valid tanpa teks lain:
{"vegetable_type_id": <int atau null>, "type_confidence": <0..1>, "grade": "A|B|C", "grade_confidence": <0..1>, "reason": "<satu kalimat bahasa Indonesia>"}
PROMPT;
    }

    /** Membersihkan balasan model dan memastikan nilainya masuk akal. */
    protected function normalize(string $raw, array $knownTypes): array
    {
        $start = strpos($raw, '{');
        $end = strrpos($raw, '}');
        if ($start === false || $end === false) {
            throw new RuntimeException('Balasan AI bukan JSON.');
        }

        $data = json_decode(substr($raw, $start, $end - $start + 1), true);
        if (! is_array($data)) {
            throw new RuntimeException('JSON balasan AI tidak valid.');
        }

        $grade = strtoupper((string) ($data['grade'] ?? ''));
        if (! in_array($grade, ['A', 'B', 'C'], true)) {
            throw new RuntimeException('Grade dari AI tidak dikenali.');
        }

        $validIds = array_column($knownTypes, 'id');
        $typeId = isset($data['vegetable_type_id']) ? (int) $data['vegetable_type_id'] : null;
        if (! in_array($typeId, $validIds, true)) {
            $typeId = null;
        }

        $clamp = fn ($v) => round(max(0, min(1, (float) $v)), 4);

        return [
            'vegetable_type_id' => $typeId,
            'type_confidence' => $clamp($data['type_confidence'] ?? 0),
            'grade' => $grade,
            'grade_confidence' => $clamp($data['grade_confidence'] ?? 0),
            'is_sellable' => $grade !== 'C',
            'reason' => mb_substr((string) ($data['reason'] ?? ''), 0, 255),
        ];
    }
}