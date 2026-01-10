<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Minute extends Model
{
    use HasFactory;

    // 👇 ADD THIS LINE TO PREVENT CRASHES
    protected $fillable = ['appointment_id', 'note'];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
}
