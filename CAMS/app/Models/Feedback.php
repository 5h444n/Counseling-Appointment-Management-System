<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    protected $fillable = [
        'appointment_id',
        'student_id',
        'advisor_id',
        'rating',
        'comment',
        'is_anonymous'
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
    
    public function advisor()
    {
        return $this->belongsTo(User::class, 'advisor_id');
    }
}
