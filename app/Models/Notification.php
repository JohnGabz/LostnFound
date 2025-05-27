<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $primaryKey = 'notification_id';

    protected $fillable = [
        'user_id',
        'type',
        'message',
        'is_read',
        'created_at', // ADDED: Need this to be fillable since timestamps are disabled
    ];

    // FIXED: Set timestamps to true and manage created_at properly
    public $timestamps = true;
    const UPDATED_AT = null; // Only use created_at

    protected $casts = [
        'is_read' => 'boolean',
        'created_at' => 'datetime',
    ];

    // Relation to User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}