<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CertificateTemplate extends Model
{
    protected $table = 'certificate_templates';

    protected $fillable = [
        'name',
        'code',
        'description',
        'file_path',
        'settings',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'settings'  => 'array',   // settings JSON -> array
        'is_active' => 'boolean',
    ];

    /**
     * Template dipakai oleh banyak event
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'certificate_template_id');
    }

    /**
     * (Opsional) siapa yang membuat template
     * kalau kamu punya kolom created_by dan model User
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}