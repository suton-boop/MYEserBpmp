<?php

namespace App\Domain\Certificates\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class AuditLog extends Model
{
    use HasUuids;

    protected $fillable = [
        'event_type','subject_id','subject_type',
        'actor_id','actor_ip','actor_user_agent',
        'metadata','prev_hash','hash',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];
}