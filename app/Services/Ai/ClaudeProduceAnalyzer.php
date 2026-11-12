<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class ClaudeProduceAnalyzer extends AbstractProduceAnalyzer
{
    public function __construct(private string $apiKey, private string $model, private int $timeout = 20) {}

    public function label(): string { return "claude:{$this->model}"; }

    public function framework(): string { return 'Anthropic API'; }

    public function analyze(string $imageBytes, string $mime, array $knownTypes): array
    {
        if ($this->apiKey === '') {
            throw new RuntimeException('ANTHROPIC_API_KEY belum diisi.');
        }

        $response = Http::timeout($this->timeout)
            ->retry(1, 300, throw: false)
            ->withHeaders(['x-api-key' => $this->apiKey, 'anthropic-version' => '2023-06-01'])
            ->post('https://api.anthropic.com/v1/messages', [
                'model' => $this->model,
                'max_tokens' => 400,
                'messages' => [[
                    'role' => 'user',
                    'content' => [
                        ['type' => 'image', 'source' => ['type' => 'base64', 'media_type' => $mime, 'data' => base64_encode($imageBytes)]],
                        ['type' => 'text', 'text' => $this->prompt($knownTypes)],
                    ],
                ]],
            ])
            ->throw();

        $text = data_get($response->json(), 'content.0.text');
        if (! is_string($text)) {
            throw new RuntimeException('Claude tidak mengembalikan teks.');
        }

        return $this->normalize($text, $knownTypes);
    }
}