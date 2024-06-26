<?php

namespace $namespace;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class nSidebarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('n_sidebars')->insert([]);
    }
}
