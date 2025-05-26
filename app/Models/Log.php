<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    use HasFactory;

    protected $primaryKey = 'log_id';
    public $timestamps = false; // Because we are using a custom `timestamp` column

    protected $fillable = [
        'user_id',
        'action',
        'timestamp',
        'details',
    ];

    /**
     * Get the user associated with the log entry.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
