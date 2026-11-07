<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Setup Test | {{ config('app.name') }}</title>

    @vite(['resources/js/app.js', 'resources/js/user/home.js'])
</head>
<body>
    <main class="container py-5" style="max-width: 720px;">
        <h1 class="h3 mb-1 brand-logo">
            <i class="bi bi-newspaper me-1"></i>Portal<span>Berita</span>
        </h1>
        <p class="text-muted">Halaman tes setup STEP 1 (akan diganti di STEP 12).</p>

        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <h2 class="h6">1. Bootstrap 5 dan Icons</h2>
                <button class="btn btn-brand" type="button">
                    <i class="bi bi-check-circle me-1"></i>Tombol Bootstrap
                </button>
                <span class="badge bg-success ms-2">Badge</span>
            </div>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <h2 class="h6">2. SweetAlert2</h2>
                <button id="btn-test-swal" class="btn btn-outline-primary" type="button">
                    Tes SweetAlert2
                </button>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="h6">3. Fetch API, Laravel API, dan MySQL</h2>
                <button id="btn-test-api" class="btn btn-outline-success mb-3" type="button">
                    Tes GET /api/ping
                </button>
                <div id="api-result"></div>
            </div>
        </div>
    </main>
</body>
</html>