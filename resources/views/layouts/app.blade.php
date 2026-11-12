<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>@yield('title', 'Dashboard') - LumbungPintar</title>

    {{-- Penjaga: tanpa token, kembali ke login sebelum halaman dirender. --}}
    <script>
        if (!localStorage.getItem('lp_token') || !localStorage.getItem('lp_user')) location.replace('/login');
    </script>

    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="{{ asset('template/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
    <link href="{{ asset('template/css/sb-admin-2.min.css') }}" rel="stylesheet">

    <style>
    /* ==================== VARIABLES ==================== */
    :root {
        --primary: #1e7e4f;
        --primary-light: #2fa56a;
        --primary-dark: #14532d;
        --primary-soft: #e8f5ee;
        --danger: #ef4444;
        --danger-soft: #fef2f2;
        --dark: #1e293b;
        --gray: #64748b;
        --gray-light: #f1f5f9;
        --border: #e2e8f0;
        --sidebar-w: 230px;
        --sidebar-icon-w: 72px;
        --topbar-h: 62px;
        --radius: 10px;
    }

    /* Item menu per peran disembunyikan sampai JS memastikan perannya cocok */
    [data-roles]:not(.role-ok) { display: none !important; }

    /* ==================== BASE ==================== */
    *, *::before, *::after { box-sizing: border-box; }
    html, body { height: 100%; }
    body { font-family: 'Sora', sans-serif !important; background: #f8fafc; margin: 0; padding: 0; }

    /* ==================== LAYOUT ==================== */
    #wrapper { display: flex; width: 100%; min-height: 100vh; }
    #content-wrapper { display: flex; flex-direction: column; flex: 1; min-width: 0; overflow-x: hidden; }
    #content { flex: 1; }

    /* ==================== SIDEBAR ==================== */
    .sidebar {
        position: sticky; top: 0; height: 100vh;
        width: var(--sidebar-w) !important;
        flex-shrink: 0; overflow-y: auto; overflow-x: hidden;
        transition: width 0.25s ease;
        background: linear-gradient(180deg, var(--primary) 0%, var(--primary-dark) 100%) !important;
        z-index: 100;
    }
    .sidebar.toggled { width: var(--sidebar-icon-w) !important; }
    .sidebar.toggled .sidebar-brand-text,
    .sidebar.toggled .sidebar-heading,
    .sidebar.toggled .nav-item .nav-link span { display: none !important; }
    .sidebar.toggled .nav-item .nav-link {
        justify-content: center !important; padding: 0.75rem 0 !important;
        margin: 0.15rem 0.4rem !important; gap: 0 !important;
    }
    .sidebar.toggled .sidebar-brand { justify-content: center !important; padding-left: 0 !important; }
    .sidebar.toggled .sidebar-divider { margin: 0.4rem 0.6rem !important; }

    .sidebar-brand {
        display: flex !important; align-items: center;
        padding: 1.25rem 1rem 1.25rem 1.25rem; font-weight: 700;
        text-decoration: none !important; color: white !important;
        white-space: nowrap; gap: 0.6rem;
    }
    .sidebar-brand-icon i { font-size: 1.6rem; flex-shrink: 0; }
    .sidebar-brand-text { font-size: 1rem; font-weight: 700; }

    .sidebar-heading {
        font-size: 0.62rem; font-weight: 700; letter-spacing: 2px; text-transform: uppercase;
        color: rgba(255,255,255,0.38); padding: 0.5rem 1.25rem 0.2rem; white-space: nowrap;
    }
    .sidebar-divider { border: none; border-top: 1px solid rgba(255,255,255,0.1); margin: 0.4rem 1rem; }

    .sidebar .nav-item { list-style: none; }
    .sidebar .nav-item .nav-link {
        display: flex; align-items: center; font-weight: 500; font-size: 0.875rem;
        padding: 0.65rem 0.9rem; margin: 0.15rem 0.75rem; border-radius: var(--radius);
        color: rgba(255,255,255,0.82) !important; text-decoration: none !important;
        white-space: nowrap; transition: background 0.18s, color 0.18s; gap: 0.65rem;
    }
    .sidebar .nav-item .nav-link i { font-size: 0.9rem; width: 1.2rem; text-align: center; flex-shrink: 0; }
    .sidebar .nav-item.active .nav-link { background: rgba(255,255,255,0.16); color: #fff !important; font-weight: 600; }
    .sidebar .nav-item .nav-link:hover { background: rgba(255,255,255,0.1); color: #fff !important; }

    /* ==================== TOPBAR ==================== */
    .topbar {
        position: sticky; top: 0; z-index: 99; height: var(--topbar-h);
        background: #fff; border-bottom: 1px solid var(--border);
        display: flex; align-items: center; padding: 0 1.25rem; gap: 0.75rem;
        flex-shrink: 0; box-shadow: 0 1px 6px rgba(0,0,0,0.04);
    }
    .btn-hamburger {
        width: 38px; height: 38px; border-radius: 10px; border: 1px solid var(--border);
        background: transparent; color: var(--gray);
        display: flex; align-items: center; justify-content: center;
        cursor: pointer; flex-shrink: 0; padding: 0; font-size: 0.9rem; line-height: 1;
        transition: background 0.18s, color 0.18s, border-color 0.18s;
    }
    .btn-hamburger:hover { background: var(--primary-soft); color: var(--primary); border-color: var(--primary-soft); }
    .btn-hamburger:focus { outline: none; }
    .topbar-right { margin-left: auto; display: flex; align-items: center; }

    .nav-item.dropdown .nav-link {
        display: flex; align-items: center; gap: 0.5rem; padding: 0.3rem 0.5rem;
        border-radius: 50px; color: var(--dark) !important; text-decoration: none !important;
    }
    .img-profile {
        width: 36px; height: 36px; border-radius: 50%; object-fit: cover;
        border: 2px solid white; box-shadow: 0 2px 6px rgba(0,0,0,0.12); flex-shrink: 0;
    }
    #navName { font-size: 0.85rem; font-weight: 600; color: var(--dark); white-space: nowrap; }

    .dropdown-menu {
        border: none; box-shadow: 0 8px 24px rgba(0,0,0,0.11); border-radius: 14px;
        padding: 0.5rem; min-width: 200px; margin-top: 0.5rem !important;
    }
    .dropdown-item {
        border-radius: 8px; padding: 0.5rem 0.9rem; font-size: 0.875rem;
        display: flex; align-items: center; gap: 0.6rem; color: var(--dark); transition: background 0.15s;
    }
    .dropdown-item i { width: 1.1rem; color: var(--gray); font-size: 0.82rem; }
    .dropdown-item:hover { background: var(--primary-soft); color: var(--primary); }
    .dropdown-item:hover i { color: var(--primary); }
    .dropdown-item.text-danger { color: var(--danger) !important; }
    .dropdown-item.text-danger i { color: var(--danger) !important; }
    .dropdown-item.text-danger:hover { background: var(--danger-soft); }
    .dropdown-divider { margin: 0.3rem 0.5rem; border-color: var(--border); }

    /* ==================== CONTENT / FOOTER ==================== */
    .container-fluid { padding: 1.5rem 1.5rem 2rem; }
    .sticky-footer { background: white; border-top: 1px solid var(--border); padding: 0.875rem 0; margin-top: auto; }
    .copyright { color: var(--gray); font-size: 0.85rem; text-align: center; }
    .scroll-to-top {
        background: var(--primary); width: 38px; height: 38px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center; color: white; text-decoration: none;
        position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 500;
        box-shadow: 0 4px 12px rgba(30,126,79,0.35); transition: background 0.18s, transform 0.18s;
    }
    .scroll-to-top:hover { background: var(--primary-light); transform: translateY(-2px); color: white; text-decoration: none; }

    /* ==================== GLOBAL OVERRIDES ==================== */
    .btn-primary { background: var(--primary); border-color: var(--primary); }
    .btn-primary:hover { background: var(--primary-light); border-color: var(--primary-light); }
    .btn-danger { background: var(--danger); border-color: var(--danger); }
    .form-control { border: 1px solid var(--border); border-radius: var(--radius); padding: 0.55rem 0.9rem; font-size: 0.9rem; }
    .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(30,126,79,0.12); }
    .modal-content { border: none; border-radius: 16px; box-shadow: 0 20px 48px rgba(0,0,0,0.12); }
    .modal-header { border-bottom: 1px solid var(--border); padding: 1.1rem 1.4rem; }
    .modal-title { font-weight: 700; font-size: 1rem; color: var(--dark); }
    .modal-body { padding: 1.4rem; }
    .modal-footer { border-top: 1px solid var(--border); padding: 1rem 1.4rem; }
    #previewPhoto {
        width: 90px; height: 90px; object-fit: cover; border-radius: 50%;
        border: 3px solid #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    /* ==================== OVERLAY (mobile) ==================== */
    #sidebarOverlay {
        display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.42);
        z-index: 1049; opacity: 0; transition: opacity 0.25s ease;
    }
    #sidebarOverlay.show { display: block; opacity: 1; }

    #sidebarToggleTop, #sidebarToggle { display: none !important; }

    /* ==================== DESKTOP (>= 1200px) ==================== */
    @media (min-width: 1200px) {
        .container-fluid { padding: 1.75rem 2rem 2rem; }
        #navName { display: inline !important; }
    }

    /* ==================== TABLET (768 - 1199px): sidebar hanya ikon ==================== */
    @media (min-width: 768px) and (max-width: 1199.98px) {
        .sidebar { width: var(--sidebar-icon-w) !important; }
        .sidebar .sidebar-brand-text,
        .sidebar .sidebar-heading,
        .sidebar .nav-item .nav-link span { display: none !important; }
        .sidebar .nav-item .nav-link {
            justify-content: center !important; padding: 0.75rem 0 !important;
            margin: 0.15rem 0.4rem !important; gap: 0 !important;
        }
        .sidebar .sidebar-brand { justify-content: center !important; padding-left: 0 !important; }
        .sidebar .sidebar-divider { margin: 0.4rem 0.6rem !important; }

        #sidebarToggle { display: flex !important; }
        #sidebarToggleTop { display: none !important; }

        /* Saat diperluas lewat tombol */
        .sidebar.toggled { width: var(--sidebar-w) !important; }
        .sidebar.toggled .sidebar-brand-text,
        .sidebar.toggled .nav-item .nav-link span { display: inline-block !important; }
        .sidebar.toggled .sidebar-heading { display: block !important; }
        .sidebar.toggled .nav-item .nav-link {
            justify-content: flex-start !important; padding: 0.65rem 0.9rem !important;
            margin: 0.15rem 0.75rem !important; gap: 0.65rem !important;
        }
        .sidebar.toggled .sidebar-brand { justify-content: flex-start !important; padding-left: 1.25rem !important; }
        .sidebar.toggled .sidebar-divider { margin: 0.4rem 1rem !important; }

        .container-fluid { padding: 1.25rem 1.25rem 1.75rem; }
        #navName { display: none !important; }

        /* Tooltip pada ikon menu */
        .sidebar:not(.toggled) .nav-item { position: relative; }
        .sidebar:not(.toggled) .nav-item .nav-link[title]:hover::after {
            content: attr(title); position: absolute;
            left: calc(var(--sidebar-icon-w) + 4px); top: 50%; transform: translateY(-50%);
            background: var(--dark); color: #fff; font-size: 0.78rem; font-weight: 500;
            padding: 0.3rem 0.65rem; border-radius: 6px; white-space: nowrap;
            z-index: 9999; pointer-events: none; box-shadow: 0 4px 12px rgba(0,0,0,0.18);
        }
    }

    /* ==================== MOBILE (< 768px): sidebar geser dari kiri ==================== */
    @media (max-width: 767.98px) {
        :root { --topbar-h: 56px; }

        .sidebar {
            position: fixed !important; top: 0; left: 0; height: 100vh !important;
            width: var(--sidebar-w) !important; z-index: 1050;
            transform: translateX(-100%);
            transition: transform 0.28s cubic-bezier(0.4,0,0.2,1) !important;
        }
        .sidebar .sidebar-brand-text,
        .sidebar .sidebar-heading,
        .sidebar .nav-item .nav-link span {
            display: inline-block !important; opacity: 1 !important; width: auto !important; overflow: visible !important;
        }
        .sidebar .nav-item .nav-link {
            justify-content: flex-start !important; padding: 0.65rem 0.9rem !important;
            margin: 0.15rem 0.75rem !important; gap: 0.65rem !important; width: auto !important;
        }
        .sidebar .sidebar-brand { justify-content: flex-start !important; padding-left: 1.25rem !important; }
        .sidebar.mobile-open { transform: translateX(0) !important; box-shadow: 8px 0 32px rgba(0,0,0,0.2); }
        .sidebar.mobile-open .nav-item.active .nav-link { background: rgba(255,255,255,0.16) !important; }

        #wrapper { display: block; width: 100%; }
        #content-wrapper { width: 100%; min-height: 100vh; }
        .topbar { padding: 0 0.875rem; gap: 0.5rem; }

        #sidebarToggleTop { display: flex !important; }
        #sidebarToggle { display: none !important; }
        #navName { display: none !important; }

        .container-fluid { padding: 1rem 0.875rem 1.5rem; }
        .modal-dialog { margin: 0.75rem; }
        .modal-content { border-radius: 12px; }
        .dropdown-menu-right { right: 0 !important; left: auto !important; }
        .img-profile { width: 32px; height: 32px; }
        .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .sticky-footer { padding: 0.6rem 0; }
        .copyright { font-size: 0.78rem; }
        .scroll-to-top { width: 34px; height: 34px; bottom: 1rem; right: 1rem; }
    }

    @media (max-width: 399.98px) {
        .topbar { padding: 0 0.65rem; }
        .container-fluid { padding: 0.875rem 0.65rem 1.25rem; }
        .modal-dialog { margin: 0.5rem; }
        .btn { font-size: 0.82rem; padding: 0.4rem 0.7rem; }
    }
    </style>
</head>

<body id="page-top">

<div id="sidebarOverlay"></div>

<div id="wrapper">

    @include('layouts.sidebar')

    <div id="content-wrapper">
        <div id="content">

            @include('layouts.topbar')

            <div class="container-fluid">
                @yield('content')
            </div>
        </div>

        <footer class="sticky-footer">
            <div class="copyright">&copy; LumbungPintar {{ date('Y') }}</div>
        </footer>
    </div>
</div>

<!-- MODAL: Edit Profil -->
<div class="modal fade" id="modalProfile" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form id="formProfile" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Profil</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <img id="previewPhoto" src="{{ asset('template/img/undraw_profile.svg') }}"
                             class="rounded-circle img-thumbnail" alt="Foto profil">
                    </div>
                    <div class="form-group">
                        <label for="inputName">Nama Lengkap</label>
                        <input type="text" name="name" id="inputName" class="form-control" required>
                    </div>
                    <div class="form-group mb-0">
                        <label for="inputPhoto">Foto Profil</label>
                        <div class="custom-file">
                            <input type="file" name="photo" id="inputPhoto" class="custom-file-input" accept="image/png,image/jpeg">
                            <label class="custom-file-label" for="inputPhoto">Pilih file</label>
                        </div>
                        <small class="form-text text-muted">Format: JPG, PNG. Maks: 2MB</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL: Logout -->
<div class="modal fade" id="logoutModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Logout</h5>
                <button class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">Apakah kamu yakin ingin keluar?</div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-dismiss="modal">Tidak</button>
                <button id="btnLogout" class="btn btn-danger">Logout</button>
            </div>
        </div>
    </div>
</div>

<a class="scroll-to-top" href="#page-top" title="Kembali ke atas"><i class="fas fa-angle-up"></i></a>

<script src="{{ asset('template/vendor/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('template/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('template/vendor/jquery-easing/jquery.easing.min.js') }}"></script>
<script src="{{ asset('template/js/sb-admin-2.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
/**
 * SIDEBAR CONTROLLER
 * Clone node trick: membuang semua listener bawaan sb-admin-2, lalu memasang listener bersih.
 */
document.addEventListener('DOMContentLoaded', function () {
    var MOBILE_BP = 768;
    var sidebar = document.getElementById('accordionSidebar');
    var overlay = document.getElementById('sidebarOverlay');
    var isOpen = false;

    function freshClone(id) {
        var el = document.getElementById(id);
        if (!el) return null;
        var clone = el.cloneNode(true);
        el.parentNode.replaceChild(clone, el);
        return clone;
    }

    var btnMobile = freshClone('sidebarToggleTop');
    var btnDesktop = freshClone('sidebarToggle');
    if (!sidebar) return;

    function openSidebar() {
        isOpen = true;
        sidebar.classList.add('mobile-open');
        overlay.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
    function closeSidebar() {
        isOpen = false;
        sidebar.classList.remove('mobile-open');
        overlay.classList.remove('show');
        document.body.style.overflow = '';
    }

    if (btnMobile) btnMobile.addEventListener('click', function (e) { e.stopPropagation(); isOpen ? closeSidebar() : openSidebar(); });
    if (btnDesktop) btnDesktop.addEventListener('click', function (e) { e.stopPropagation(); sidebar.classList.toggle('toggled'); });
    if (overlay) overlay.addEventListener('click', closeSidebar);

    sidebar.querySelectorAll('.nav-link').forEach(function (link) {
        link.addEventListener('click', function () { if (window.innerWidth < MOBILE_BP) closeSidebar(); });
    });
    window.addEventListener('resize', function () { if (window.innerWidth >= MOBILE_BP && isOpen) closeSidebar(); });
});
</script>

<script>
const DEFAULT_PHOTO = "{{ asset('template/img/undraw_profile.svg') }}";

window.LP = {
    token: localStorage.getItem('lp_token'),
    user: JSON.parse(localStorage.getItem('lp_user')),
    rp: n => 'Rp ' + Number(n || 0).toLocaleString('id-ID'),
    esc: s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c])),
    clear() { localStorage.removeItem('lp_token'); localStorage.removeItem('lp_user'); },

    async api(path, opt = {}) {
        const headers = { Accept: 'application/json', Authorization: 'Bearer ' + LP.token };
        let body;
        if (opt.form) body = opt.form;                       // FormData: biarkan browser mengatur Content-Type
        else if (opt.body) { headers['Content-Type'] = 'application/json'; body = JSON.stringify(opt.body); }

        const res = await fetch('/api' + path, { method: opt.method || 'GET', headers, body });
        if (res.status === 401) { LP.clear(); location.replace('/login'); throw new Error('Sesi berakhir.'); }
        if (res.status === 204) return null;

        const data = await res.json().catch(() => ({}));
        if (!res.ok) {
            throw new Error(data.errors ? Object.values(data.errors).flat().join(' ') : (data.message || 'Terjadi kesalahan (' + res.status + ')'));
        }
        return data;
    },

    async logout() {
        try { await LP.api('/auth/logout', { method: 'POST' }); } catch (e) { /* tetap keluar */ }
        LP.clear();
        location.replace('/login');
    },
};

$(function () {
    const Toast = Swal.mixin({ toast: true, position: 'center', showConfirmButton: false, timer: 3000, timerProgressBar: true });
    const role = (LP.user && LP.user.role) || '';

    // Tampilkan hanya menu sesuai peran
    $('[data-roles]').each(function () {
        if (this.dataset.roles.split(' ').includes(role)) $(this).addClass('role-ok');
    });

    function paintUser(u) {
        const photo = u.photo ? u.photo + '?t=' + Date.now() : DEFAULT_PHOTO;
        $('#navName').text(u.name);
        $('#navPhoto').attr('src', photo);
        $('#previewPhoto').attr('src', photo);
        $('#inputName').val(u.name);
    }

    // Tampilkan data tersimpan dulu, lalu segarkan dari server
    paintUser(LP.user);
    LP.api('/auth/me').then(r => {
        LP.user = r.data;
        localStorage.setItem('lp_user', JSON.stringify(r.data));
        paintUser(r.data);
    }).catch(() => {});

    // Logout
    $('#btnLogout').on('click', () => LP.logout());

    // Pratinjau foto
    $('#inputPhoto').on('change', function (e) {
        const file = e.target.files[0];
        if (!file) return;
        if (file.size > 2 * 1024 * 1024) {
            Toast.fire({ icon: 'error', title: 'Ukuran file terlalu besar! Maksimal 2MB.' });
            this.value = ''; return;
        }
        if (!['image/jpeg', 'image/png'].includes(file.type)) {
            Toast.fire({ icon: 'error', title: 'Format tidak didukung! Gunakan JPG atau PNG.' });
            this.value = ''; return;
        }
        $('#previewPhoto').attr('src', URL.createObjectURL(file));
        $('.custom-file-label').text(file.name);
    });

    // Simpan profil
    $('#formProfile').on('submit', async function (e) {
        e.preventDefault();
        try {
            const r = await LP.api('/profile', { method: 'POST', form: new FormData(this) });
            LP.user = r.data;
            localStorage.setItem('lp_user', JSON.stringify(r.data));
            paintUser(r.data);
            $('#modalProfile').modal('hide');
            Toast.fire({ icon: 'success', title: 'Profil berhasil diperbarui' });
        } catch (err) {
            Toast.fire({ icon: 'error', title: err.message || 'Gagal memperbarui profil' });
        }
    });
});
</script>

@yield('scripts')
</body>
</html>