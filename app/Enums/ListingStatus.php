<?php

namespace App\Enums;

enum ListingStatus: string
{
    case Draft = 'draft';
    case MenungguKonfirmasi = 'menunggu_konfirmasi_petani';
    case Tayang = 'tayang';
    case Habis = 'habis';
    case Kedaluwarsa = 'kedaluwarsa';
    case Ditarik = 'ditarik';
}