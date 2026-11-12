<nav class="topbar">
    {{-- Mobile toggle --}}
    <button id="sidebarToggleTop" class="btn-hamburger" aria-label="Buka menu">
        <i class="fas fa-bars"></i>
    </button>

    {{-- Tablet toggle --}}
    <button id="sidebarToggle" class="btn-hamburger" aria-label="Toggle sidebar">
        <i class="fas fa-bars"></i>
    </button>

    <div class="topbar-right">
        <ul class="navbar-nav">
            <li class="nav-item dropdown no-arrow">
                <a class="nav-link dropdown-toggle" href="#" id="userDropdown" data-toggle="dropdown"
                   aria-haspopup="true" aria-expanded="false">
                    <span id="navName" class="mr-2">Memuat...</span>
                    <img id="navPhoto" class="img-profile rounded-circle"
                         src="{{ asset('template/img/undraw_profile.svg') }}" alt="Profil">
                </a>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                    <a class="dropdown-item" href="#" data-toggle="modal" data-target="#modalProfile">
                        <i class="fas fa-user-edit fa-fw"></i> Edit Profil
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item text-danger" href="#" data-toggle="modal" data-target="#logoutModal">
                        <i class="fas fa-sign-out-alt fa-fw"></i> Logout
                    </a>
                </div>
            </li>
        </ul>
    </div>
</nav>