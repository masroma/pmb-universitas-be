<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PmbSyncedRegistrationPeriod extends Model
{
    protected $fillable = [
        'sevima_id',
        'nama_periode_pendaftaran',
        'tanggal_awal_pendaftaran',
        'tanggal_akhir_pendaftaran',
        'status_periode_pendaftaran',
        'id_status_periode_pendaftaran',
        'keterangan',
        'is_active',
        'raw_payload',
        'synced_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'tanggal_awal_pendaftaran' => 'date',
            'tanggal_akhir_pendaftaran' => 'date',
            'raw_payload' => 'array',
            'synced_at' => 'datetime',
        ];
    }
}
