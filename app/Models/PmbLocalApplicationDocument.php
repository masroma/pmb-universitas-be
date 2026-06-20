<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PmbLocalApplicationDocument extends Model
{
    protected $fillable = [
        'pmb_local_application_id',
        'type',
        'original_name',
        'path',
        'mime_type',
        'size',
    ];

    protected $appends = [
        'url',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(PmbLocalApplication::class, 'pmb_local_application_id');
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->path);
    }
}
