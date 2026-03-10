<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalLog extends Model
{
    use HasUuids;

    protected $fillable = [
        'certificate_id', 'level', 'action', 'note',
        'acted_by', 'acted_ip', 'acted_user_agent',
    ];

    public function certificate(): BelongsTo
    {
        return $this->belongsTo(Certificate::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class , 'acted_by');
    }
}
