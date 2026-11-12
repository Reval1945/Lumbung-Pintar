@extends('layouts.auth')

@section('title', 'Masuk')

@section('content')
    <div class="col-lg-6 form-side">
        <h1>Selamat Datang</h1>
        <div class="subtitle">Masuk untuk mengelola tawaran dan pesanan Anda</div>

        <form id="loginForm" novalidate>
            <div class="form-group">
                <label><i class="fas fa-user" style="margin-right:6px;color:var(--primary)"></i>Nomor HP atau Email</label>
                <input type="text" id="login" name="login" class="form-control" placeholder="Masukkan nomor HP atau email" autocomplete="username" required>
            </div>

            <div class="form-group">
                <label><i class="fas fa-lock" style="margin-right:6px;color:var(--primary)"></i>Kata Sandi</label>
                <div class="input-wrapper">
                    <input type="password" id="password" name="password" class="form-control" placeholder="Masukkan kata sandi" autocomplete="current-password" required>
                    <span class="password-toggle"><i class="fas fa-eye"></i></span>
                </div>
            </div>

            <button type="submit" class="btn-main" id="submitBtn" style="margin-top:28px">
                <i class="fas fa-sign-in-alt"></i>
                <span id="buttonText">Masuk</span>
                <span id="loadingSpinner" class="d-none"><span class="spinner-border spinner-border-sm" role="status"></span></span>
            </button>
        </form>

        <div class="switch-section">
            Belum punya akun? <a href="{{ url('/register') }}">Daftar sekarang</a>
        </div>
    </div>

    <div class="col-lg-6 art-side">
        @include('layouts.auth-art', [
            'title' => 'LumbungPintar',
            'text' => 'Pasar pangan terencana yang menghubungkan petani lokal dengan pembeli institusional.',
        ])
    </div>
@endsection

@push('scripts')
<script>
$('#loginForm').on('submit', async function (e) {
    e.preventDefault();
    LPAuth.clearErrors(this);
    LPAuth.loading(true, 'Masuk');

    try {
        const res = await fetch('/api/auth/login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ login: $('#login').val().trim(), password: $('#password').val() }),
        });
        const data = await res.json().catch(() => ({}));
        if (!res.ok) throw new Error(LPAuth.firstError(data));

        LPAuth.save(data.token, data.user);
        location.href = '/dashboard';
    } catch (err) {
        Swal.fire({ icon: 'error', title: 'Gagal masuk', text: LPAuth.networkMessage(err), confirmButtonColor: '#1e7e4f' });
        LPAuth.loading(false, 'Masuk');
    }
});
</script>
@endpush