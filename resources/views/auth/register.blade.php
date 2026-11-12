@extends('layouts.auth')

@section('title', 'Daftar')

@section('content')
    <div class="col-lg-6 art-side">
        @include('layouts.auth-art', [
            'title' => 'Bergabunglah<br>Dengan Kami',
            'text' => 'Jual hasil panen langsung ke dapur SPPG, katering, dan koperasi, atau belanja pasokan segar dari petani lokal.',
        ])
    </div>

    <div class="col-lg-6 form-side">
        <h1>Buat Akun</h1>
        <div class="subtitle">Isi data diri Anda untuk mendaftar</div>

        <form id="registerForm" novalidate>
            <div class="form-grid">
                <div class="form-group">
                    <label><i class="fas fa-user" style="margin-right:6px;color:var(--primary)"></i>Nama Lengkap</label>
                    <input type="text" class="form-control" name="name" placeholder="Nama lengkap" required>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-phone" style="margin-right:6px;color:var(--primary)"></i>Nomor HP</label>
                    <input type="text" class="form-control" name="phone" placeholder="Nomor HP" required>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-envelope" style="margin-right:6px;color:var(--primary)"></i>Email <span class="optional-badge">opsional</span></label>
                    <input type="email" class="form-control" name="email" placeholder="Alamat email">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-user-tag" style="margin-right:6px;color:var(--primary)"></i>Daftar Sebagai</label>
                    <select class="form-control" name="role" required>
                        <option value="petani">Petani</option>
                        <option value="agen_desa">Agen Desa</option>
                        <option value="pembeli">Pembeli (SPPG, katering, koperasi)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-lock" style="margin-right:6px;color:var(--primary)"></i>Kata Sandi</label>
                    <div class="input-wrapper">
                        <input type="password" class="form-control" name="password" placeholder="Minimal 8 karakter" autocomplete="new-password" required>
                        <span class="password-toggle"><i class="fas fa-eye"></i></span>
                    </div>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-lock" style="margin-right:6px;color:var(--primary)"></i>Ulangi Kata Sandi</label>
                    <div class="input-wrapper">
                        <input type="password" class="form-control" name="password_confirmation" placeholder="Ulangi kata sandi" autocomplete="new-password" required>
                        <span class="password-toggle"><i class="fas fa-eye"></i></span>
                    </div>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-map-marker-alt" style="margin-right:6px;color:var(--primary)"></i>Desa <span class="optional-badge">opsional</span></label>
                    <input type="text" class="form-control" name="desa" placeholder="Desa">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-map" style="margin-right:6px;color:var(--primary)"></i>Kecamatan <span class="optional-badge">opsional</span></label>
                    <input type="text" class="form-control" name="kecamatan" placeholder="Kecamatan">
                </div>
            </div>

            <button type="submit" class="btn-main" id="submitBtn">
                <i class="fas fa-user-plus"></i>
                <span id="buttonText">Daftar</span>
                <span id="loadingSpinner" class="d-none"><span class="spinner-border spinner-border-sm" role="status"></span></span>
            </button>
        </form>

        <div class="switch-section">
            Sudah punya akun? <a href="{{ url('/login') }}">Masuk</a>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$('#registerForm').on('submit', async function (e) {
    e.preventDefault();
    LPAuth.clearErrors(this);

    // Kolom opsional yang kosong tidak dikirim.
    const payload = {};
    new FormData(this).forEach((v, k) => { if (String(v).trim() !== '') payload[k] = String(v).trim(); });

    if (payload.password !== payload.password_confirmation) {
        LPAuth.fieldErrors(this, { password_confirmation: ['Kata sandi tidak sama.'] });
        return;
    }

    LPAuth.loading(true, 'Daftar');

    try {
        const res = await fetch('/api/auth/register', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(payload),
        });
        const data = await res.json().catch(() => ({}));

        if (!res.ok) {
            LPAuth.fieldErrors(this, data.errors);
            Swal.fire({ icon: 'error', title: 'Ups!', text: LPAuth.firstError(data), confirmButtonColor: '#E63946' });
            LPAuth.loading(false, 'Daftar');
            return;
        }

        LPAuth.save(data.token, data.user);
        const needsVerify = data.user.role !== 'petani';
        Swal.fire({
            icon: 'success',
            title: 'Registrasi Berhasil!',
            text: needsVerify
                ? 'Akun Anda dibuat dan menunggu verifikasi admin mitra. Beberapa fitur baru aktif setelah akun diverifikasi.'
                : 'Akun Anda telah dibuat.',
            confirmButtonColor: '#1e7e4f',
        }).then(() => { location.href = '/dashboard'; });
    } catch (err) {
        Swal.fire({ icon: 'error', title: 'Kesalahan Server', text: LPAuth.networkMessage(err), confirmButtonColor: '#E63946' });
        LPAuth.loading(false, 'Daftar');
    }
});
</script>
@endpush