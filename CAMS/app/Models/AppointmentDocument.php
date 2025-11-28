<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppointmentDocument extends Model
{
    use HasFactory;

    protected $fillable = ['appointment_id', 'file_path', 'original_name'];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
}
