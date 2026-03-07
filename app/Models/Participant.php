<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Participant extends Model
{
    protected $fillable = [
        'event_id', 'custom_date', 'name', 'email', 'nik', 'institution', 'daerah', 'jenjang', 'peran', 'keterangan', 'status', 'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
        'custom_date' => 'date',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    // Sertifikat terakhir (default: berdasarkan updated_at)
    public function latestCertificate(): HasOne
    {
        return $this->hasOne(Certificate::class)->latestOfMany('updated_at');
    }

    // Atribut tampilan status peserta (fallback draft)
    public function getDisplayStatusAttribute(): string
    {
        return $this->latestCertificate->status ?? 'draft';
    }
}