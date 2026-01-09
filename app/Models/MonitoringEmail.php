<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonitoringEmail extends Model
{
    protected $table = 'monitoring_emails';

    protected $fillable = [
        'from',
        'to',
        'cc',
        'bcc',
        'subject',
        'content_html',
        'content_text',
        'action_type',
        'action_by_id',
        'action_at',
    ];

    protected $casts = [
        'action_at' => 'datetime',
    ];

    public function actionBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'action_by_id');
    }
}
