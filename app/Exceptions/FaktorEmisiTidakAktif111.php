<?php

namespace App\Exceptions;

use RuntimeException;

// Dilempar KF-20 saat faktor emisi yang dipakai sudah diarsipkan (is_active = 0).
class FaktorEmisiTidakAktif111 extends RuntimeException
{
    public function __construct(public readonly string $namaFaktor = '')
    {
        parent::__construct("Faktor emisi \"{$namaFaktor}\" sudah diarsipkan dan tidak bisa dipakai menghitung.");
    }
}
