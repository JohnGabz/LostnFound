<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Claim extends Model
{
    use HasFactory;

    protected $primaryKey = 'claim_id';
    
    protected $fillable = [
        'item_id',
        'claimer_id',  // user who claimed the item
        'status',      // e.g., 'pending', 'approved', 'rejected'
        'message',
        'photo_path',  // optional photo upload related to claim
    ];

    /**
     * The item this claim is for.
     */
    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id', 'item_id');
    }

    /**
     * The user who made the claim.
     */
    public function claimer()
    {
        return $this->belongsTo(User::class, 'claimer_id');
    }
}
