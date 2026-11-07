<?php

namespace App\Enums;

enum UserRole: string
{
    case Petani = 'petani';
    case AgenDesa = 'agen_desa';
    case Pembeli = 'pembeli';
    case AdminMitra = 'admin_mitra';
    case Admin = 'admin';
}