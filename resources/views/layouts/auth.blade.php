<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>@yield('title') - LumbungPintar</title>

    <link href="{{ asset('template/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
    <link href="{{ asset('template/css/sb-admin-2.min.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,800,900" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root {
            --primary: #1e7e4f; --primary-light: #2fa56a; --dark-green: #14532d;
            --gray: #6C757D; --border-color: #E0E0E0; --accent: #E63946; --radius: 8px;
        }
        html, body { height: 100%; width: 100%; font-family: 'Inter', sans-serif; background: #fff; }
        .container-fluid { height: 100%; width: 100%; padding: 0; }
        .row { height: 100%; width: 100%; margin: 0; }
        [class*="col-"] { padding: 0; }

        /* Sisi form */
        .form-side {
            height: 100vh; display: flex; flex-direction: column; justify-content: center;
            padding: 30px 70px; overflow-y: auto; background: #fff;
        }
        .form-side h1 { font-weight: 700; font-size: 34px; color: #000; margin-bottom: 6px; letter-spacing: -.5px; }
        .form-side .subtitle { color: var(--gray); font-size: 15px; margin-bottom: 28px; }
        .form-group { margin-bottom: 20px; }
        .form-group label {
            font-weight: 600; font-size: 12px; color: #000; margin-bottom: 6px;
            display: block; letter-spacing: .3px; text-transform: uppercase;
        }
        .form-control {
            width: 100%; height: 44px; border: 1px solid var(--border-color); border-radius: var(--radius);
            padding: 0 14px; font-size: 14px; font-family: 'Inter', sans-serif; background: #fff; transition: all .2s;
        }
        .form-control:focus { border-color: var(--primary); outline: none; box-shadow: 0 0 0 3px rgba(30,126,79,.12); }
        .form-control::placeholder { color: #B0B0B0; font-size: 13px; }
        .form-control.is-invalid { border-color: var(--accent); }
        .invalid-feedback { display: block; color: var(--accent); font-size: 11px; margin-top: 4px; }
        .optional-badge {
            color: var(--gray); font-size: 10px; font-weight: 400; margin-left: 6px; background: #E9ECEF;
            padding: 2px 6px; border-radius: 4px; text-transform: lowercase;
        }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px 20px; margin-bottom: 20px; }
        .form-grid .form-group { margin-bottom: 0; }
        .input-wrapper { position: relative; width: 100%; }
        .password-toggle { position: absolute; right: 14px; top: 50%; transform: translateY(-50%); color: var(--gray); cursor: pointer; z-index: 10; }

        .btn-main {
            width: 100%; height: 46px; background: var(--primary); border: none; border-radius: var(--radius);
            color: #fff; font-weight: 600; font-size: 14px; font-family: 'Inter', sans-serif; text-transform: uppercase;
            letter-spacing: .5px; cursor: pointer; display: flex; align-items: center; justify-content: center;
            gap: 8px; transition: all .2s;
        }
        .btn-main:hover { background: var(--primary-light); transform: translateY(-1px); }
        .btn-main:disabled { opacity: .7; cursor: not-allowed; transform: none; }
        .switch-section { text-align: center; margin-top: 22px; padding-top: 22px; border-top: 1px solid var(--border-color); font-size: 14px; color: var(--gray); }
        .switch-section a { color: var(--primary); font-weight: 600; margin-left: 5px; text-decoration: none; }
        .switch-section a:hover { color: var(--primary-light); text-decoration: underline; }

        /* Sisi ilustrasi */
        .art-side {
            background: linear-gradient(135deg, var(--primary), var(--dark-green)); color: #fff; height: 100vh;
            display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden;
        }
        .art-side::before {
            content: ''; position: absolute; width: 100%; height: 100%; background-size: cover;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="none"><path d="M0 0 L100 100 L0 100 Z" fill="rgba(255,255,255,0.03)"/><path d="M100 0 L0 100 L100 100 Z" fill="rgba(255,255,255,0.03)"/></svg>');
        }
        .art-content { text-align: center; max-width: 80%; position: relative; z-index: 2; }
        .icons-container { display: flex; justify-content: center; gap: 28px; margin-bottom: 28px; }
        .icon-circle {
            width: 66px; height: 66px; background: rgba(255,255,255,.15); border-radius: 50%; display: flex;
            align-items: center; justify-content: center; backdrop-filter: blur(5px);
            border: 1px solid rgba(255,255,255,.2); animation: float 3s ease-in-out infinite;
        }
        .icon-circle:nth-child(2) { animation-delay: .5s; }
        .icon-circle:nth-child(3) { animation-delay: 1s; }
        .icon-circle i { font-size: 28px; color: #fff; }
        @keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
        .art-content h2 { font-weight: 700; font-size: 32px; margin-bottom: 14px; line-height: 1.3; }
        .art-content p { font-size: 15px; line-height: 1.6; opacity: .9; max-width: 340px; margin: 0 auto; }

        .spinner-border-sm { width: 16px; height: 16px; border-width: 2px; }

        @media (max-width: 1200px) { .form-side { padding: 30px 50px; } }
        @media (max-width: 992px) { .art-side { display: none; } .form-side { padding: 30px; } }
        @media (max-width: 576px) { .form-side { padding: 20px; } .form-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        @yield('content')
    </div>
</div>

<script src="{{ asset('template/vendor/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('template/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('template/js/sb-admin-2.min.js') }}"></script>

<script>
    // Sudah login: langsung ke dashboard. Jika token ternyata kedaluwarsa, dashboard menghapusnya dan kembali ke sini.
    if (localStorage.getItem('lp_token')) location.replace('/dashboard');

    window.LPAuth = {
        save(token, user) {
            localStorage.setItem('lp_token', token);
            localStorage.setItem('lp_user', JSON.stringify(user));
        },
        esc: s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c])),
        firstError(data) {
            return data.errors ? Object.values(data.errors).flat()[0] : (data.message || 'Terjadi kesalahan.');
        },
        loading(on, label) {
            $('#submitBtn').prop('disabled', on);
            $('#buttonText').text(on ? 'Memproses...' : label);
            $('#loadingSpinner').toggleClass('d-none', !on);
        },
        clearErrors(form) {
            $(form).find('.is-invalid').removeClass('is-invalid');
            $(form).find('.invalid-feedback').remove();
        },
        fieldErrors(form, errors) {
            Object.entries(errors || {}).forEach(([name, msgs]) => {
                const input = $(form).find('[name="' + name + '"]').first();
                if (!input.length) return;
                input.addClass('is-invalid');
                const anchor = input.closest('.input-wrapper').length ? input.closest('.input-wrapper') : input;
                anchor.after('<div class="invalid-feedback">' + LPAuth.esc(msgs[0]) + '</div>');
            });
        },
        networkMessage: err => (err instanceof TypeError ? 'Tidak dapat menghubungi server. Pastikan server berjalan.' : err.message),
    };

    $(document).on('click', '.password-toggle', function () {
        const input = $(this).siblings('input');
        input.attr('type', input.attr('type') === 'password' ? 'text' : 'password');
        $(this).find('i').toggleClass('fa-eye fa-eye-slash');
    });
</script>

@stack('scripts')
</body>
</html>