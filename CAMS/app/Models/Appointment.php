<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'slot_id',
        'token',
        'purpose',
        'status',
        'meeting_notes'
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function slot()
    {
        return $this->belongsTo(AppointmentSlot::class, 'slot_id');
    }

    public function documents()
    {
        return $this->hasMany(AppointmentDocument::class);
    }
}
