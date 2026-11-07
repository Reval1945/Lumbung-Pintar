<?php

namespace App\Enums;

enum OrderStatus: string
{
    case MenungguKonfirmasi = 'menunggu_konfirmasi';
    case Dikonfirmasi = 'dikonfirmasi';
    case Diproses = 'diproses';
    case Dikirim = 'dikirim';
    case Selesai = 'selesai';

    public function next(): ?self
    {
        return match ($this) {
            self::MenungguKonfirmasi => self::Dikonfirmasi,
            self::Dikonfirmasi => self::Diproses,
            self::Diproses => self::Dikirim,
            self::Dikirim => self::Selesai,
            self::Selesai => null,
        };
    }

    public function timestampColumn(): ?string
    {
        return match ($this) {
            self::Dikonfirmasi => 'confirmed_at',
            self::Diproses => 'processing_at',
            self::Dikirim => 'shipped_at',
            self::Selesai => 'completed_at',
            default => null,
        };
    }
}