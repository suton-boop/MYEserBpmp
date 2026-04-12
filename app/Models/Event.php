<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Event extends Model
{
    protected $table = 'events';
    
    const STATUS_PROPOSED = 'proposed';
    const STATUS_DRAFT    = 'draft';
    const STATUS_ACTIVE   = 'active';
    const STATUS_CLOSED   = 'closed';

    protected $fillable = [
        'name', 'location', 'start_date', 'end_date', 'description',
        'status', 'certificate_template_id', 'certificate_appendix', 'is_date_per_participant', 'signing_date'];
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'certificate_appendix' => 'array',
        'is_date_per_participant' => 'boolean',
        'signing_date' => 'date', ];



    /*
     |--------------------------------------------------------------------------
     | RELATIONS
     |--------------------------------------------------------------------------
     */

    // Event punya banyak peserta
    public function participants(): HasMany
    {
        return $this->hasMany(Participant::class , 'event_id');
    }

    // Event punya banyak sertifikat
    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class , 'event_id');
    }

    // Event memilih 1 template sertifikat

    public function certificateTemplate()
    {
        return $this->belongsTo(CertificateTemplate::class , 'certificate_template_id');
    }
    // pembuat event (optional)
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class , 'created_by');
    }
}
