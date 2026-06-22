<?php

namespace Database\Seeders;

use App\Models\Bank;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
   public function run(): void
{
    Bank::insert([
        ['name' => 'Commercial Bank of Ethiopia', 'code' => 'CBE'],
        ['name' => 'Bank of Abyssinia', 'code' => 'BOA'],
        ['name' => 'Dashen Bank', 'code' => 'DASHEN'],
        ['name' => 'Awash Bank', 'code' => 'AWASH'],
    ]);
}
}
