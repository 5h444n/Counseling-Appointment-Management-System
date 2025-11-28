<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Waitlist extends Model
{
    use HasFactory;

    protected $fillable = ['slot_id', 'student_id', 'is_notified'];

    public function slot()
    {
        return $this->belongsTo(AppointmentSlot::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class);
    }
}
