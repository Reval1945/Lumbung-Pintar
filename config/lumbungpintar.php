<?php

return [
    // Biaya layanan platform (proposal: 2-3%), ditambahkan ke total pembeli.
    'service_fee_percent' => (float) env('LP_SERVICE_FEE_PERCENT', 2.0),

    'ai' => [
        'driver' => env('LP_AI_DRIVER', 'gemini'),
        'timeout' => 20,
        'gemini' => [
            'key' => env('GEMINI_API_KEY'),
            'model' => env('GEMINI_MODEL', 'gemini-2.5-flash'),
        ],
        'claude' => [
            'key' => env('ANTHROPIC_API_KEY'),
            'model' => env('CLAUDE_MODEL', 'claude-haiku-5-5'),
        ],
    ],
];