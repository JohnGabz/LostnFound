<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Item extends Model
{
    use HasFactory;

    protected $primaryKey = 'item_id';

    protected $fillable = [
        'title',
        'location',
        'description',
        'category',
        'image_path',
        'status',   // 'lost' or 'found'
        'user_id',  // owner of the item
    ];

    /**
     * The user who reported the item (owner).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Claims made on this item.
     */
    public function claims()
    {
        return $this->hasMany(Claim::class, 'item_id', 'item_id');
    }
}
