<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'description',
        'ip_address',
    ];

    /**
     * Get the user that performed the action.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get a human-readable action label.
     */
    public function getActionLabelAttribute(): string
    {
        return match($this->action) {
            'login' => 'User Login',
            'book_appointment' => 'Appointment Booked',
            'cancel_appointment' => 'Appointment Cancelled',
            default => ucfirst(str_replace('_', ' ', $this->action)),
        };
    }

    /**
     * Get a badge color for the action.
     */
    public function getActionColorAttribute(): string
    {
        return match($this->action) {
            'login' => 'blue',
            'book_appointment' => 'green',
            'cancel_appointment' => 'red',
            default => 'gray',
        };
    }
}
