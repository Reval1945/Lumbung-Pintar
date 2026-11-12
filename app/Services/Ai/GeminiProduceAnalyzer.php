<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiProduceAnalyzer extends AbstractProduceAnalyzer
{
    public function __construct(private string $apiKey, private string $model, private int $timeout = 20) {}

    public function label(): string { return "gemini:{$this->model}"; }

    public function framework(): string { return 'Gemini API'; }

    public function analyze(string $imageBytes, string $mime, array $knownTypes): array
    {
        if ($this->apiKey === '') {
            throw new RuntimeException('GEMINI_API_KEY belum diisi.');
        }

        $response = Http::timeout($this->timeout)
            ->retry(1, 300, throw: false)
            ->withHeaders(['x-goog-api-key' => $this->apiKey])
            ->post("https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent", [
                'contents' => [[
                    'parts' => [
                        ['inline_data' => ['mime_type' => $mime, 'data' => base64_encode($imageBytes)]],
                        ['text' => $this->prompt($knownTypes)],
                    ],
                ]],
                'generationConfig' => ['responseMimeType' => 'application/json', 'temperature' => 0.1],
            ])
            ->throw();

        $text = data_get($response->json(), 'candidates.0.content.parts.0.text');
        if (! is_string($text)) {
            throw new RuntimeException('Gemini tidak mengembalikan teks.');
        }

        return $this->normalize($text, $knownTypes);
    }
}