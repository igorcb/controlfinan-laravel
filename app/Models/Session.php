<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// A row in the Rails-style `sessions` table; the browser keeps its id in the "session_id" cookie.
class Session extends Model
{
    protected $fillable = ['user_id', 'ip_address', 'user_agent'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
