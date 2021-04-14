<?php

namespace Database\Seeders;

use App\Sysadmin;
use Illuminate\Database\Seeder;

class SysadminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Sysadmin::factory()->create(['username' => 'admin', 'password' => bcrypt('secret')]);
    }
}
