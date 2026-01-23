<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notice extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'user_role',
        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
