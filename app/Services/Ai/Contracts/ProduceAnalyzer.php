<?php

namespace App\Services\Ai\Contracts;

interface ProduceAnalyzer
{
    /**
     * @param  array<int, array{id: int, name: string}>  $knownTypes
     * @return array{vegetable_type_id: ?int, type_confidence: float, grade: string, grade_confidence: float, is_sellable: bool, reason: string}
     */
    public function analyze(string $imageBytes, string $mime, array $knownTypes): array;

    /** Label untuk dicatat di ai_model_versions, mis. "gemini:gemini-2.5-flash". */
    public function label(): string;

    public function framework(): string;
}