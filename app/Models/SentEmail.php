<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SentEmail extends Model
{
    protected $fillable = [
        'from',
        'to',
        'cc',
        'bcc',
        'subject',
        'content_html',
        'content_text',
    ];
}
