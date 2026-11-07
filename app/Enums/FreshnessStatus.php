<?php

namespace App\Enums;

enum FreshnessStatus: string
{
    case Fresh = 'fresh';
    case SegeraDiskon = 'segera_diskon';
    case DiskonKesegaran = 'diskon_kesegaran';
    case TidakLayakJual = 'tidak_layak_jual';
}