<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AppointmentSlot;
use App\Models\User;

class AppointmentSlotSeeder extends Seeder
{
    public function run(): void
    {
        // Find all advisors
        $advisors = User::where('role', 'advisor')->get();

        foreach ($advisors as $advisor) {
            // Create 5 slots for each advisor
            $randomDay1 = rand(1, 7);
            AppointmentSlot::create([
                'advisor_id' => $advisor->id,
                'start_time' => now()->addDays($randomDay1)->setTime(10, 0), // Random day next week at 10 AM
                'end_time'   => now()->addDays($randomDay1)->setTime(11, 0),
                'status'     => 'active',
                'is_recurring' => false,
            ]);

            $randomDay2 = rand(1, 7);
            AppointmentSlot::create([
                'advisor_id' => $advisor->id,
                'start_time' => now()->addDays($randomDay2)->setTime(14, 0), // Random day next week at 2 PM
                'end_time'   => now()->addDays($randomDay2)->setTime(15, 0),
                'status'     => 'active',
                'is_recurring' => false,
            ]);
        }
    }
}
