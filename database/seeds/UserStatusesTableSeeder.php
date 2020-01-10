<?php

use Illuminate\Database\Seeder;
use App\Models\UserStatus;

class UserStatusesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $statuses = [
            ['name' => 'Active'],
            ['name' => 'Inactive'],
            ['name' => 'Blocked'],
            ['name' => 'Archived'],
            ['name' => 'Pending'],
        ];

        UserStatus::insert($statuses);
    }
}
