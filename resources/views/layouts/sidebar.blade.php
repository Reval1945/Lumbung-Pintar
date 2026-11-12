@php
    // Menu per peran. Tautan fitur sementara mengarah ke halaman demo (/demo#tab).
    // Saat halaman Blade untuk fitur itu dibuat, cukup ganti URL-nya di sini.
    $groups = [
        [
            'heading' => 'Penjualan',
            'roles'   => 'petani agen_desa',
            'items'   => [
                ['Tawaran Saya', 'fa-seedling', 'listings'],
                ['Pesanan Masuk', 'fa-inbox', 'orders'],
                ['Chat', 'fa-comments', 'chat'],
                ['Rating', 'fa-star', 'ratings'],
            ],
        ],
        [
            'heading' => 'Belanja',
            'roles'   => 'pembeli',
            'items'   => [
                ['Katalog', 'fa-store', 'catalog'],
                ['Keranjang', 'fa-shopping-cart', 'cart'],
                ['Pesanan Saya', 'fa-receipt', 'myorders'],
                ['Chat', 'fa-comments', 'chat'],
            ],
        ],
        [
            'heading' => 'Administrasi',
            'roles'   => 'admin_mitra admin',
            'items'   => [
                ['Verifikasi Akun', 'fa-user-check', 'verif'],
                ['Organisasi', 'fa-building', 'orgs'],
            ],
        ],
    ];
@endphp

<ul class="navbar-nav sidebar sidebar-dark accordion" id="accordionSidebar">

    <a class="sidebar-brand" href="{{ url('/dashboard') }}">
        <div class="sidebar-brand-icon"><i class="fas fa-seedling"></i></div>
        <div class="sidebar-brand-text">LumbungPintar</div>
    </a>

    <hr class="sidebar-divider my-0">

    <li class="nav-item {{ Request::is('dashboard') ? 'active' : '' }}">
        <a class="nav-link" href="{{ url('/dashboard') }}" title="Dashboard">
            <i class="fas fa-fw fa-tachometer-alt"></i><span>Dashboard</span>
        </a>
    </li>

    @foreach ($groups as $group)
        <hr class="sidebar-divider" data-roles="{{ $group['roles'] }}">
        <div class="sidebar-heading" data-roles="{{ $group['roles'] }}">{{ $group['heading'] }}</div>

        @foreach ($group['items'] as [$label, $icon, $tab])
            <li class="nav-item" data-roles="{{ $group['roles'] }}">
                <a class="nav-link" href="{{ url('/demo#' . $tab) }}" title="{{ $label }}">
                    <i class="fas fa-fw {{ $icon }}"></i><span>{{ $label }}</span>
                </a>
            </li>
        @endforeach
    @endforeach

    <hr class="sidebar-divider">
</ul>