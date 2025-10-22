<?php

namespace Database\Seeders;

use App\Models\Table;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            Table::firstOrCreate(
                ['table_number' => $i],
                [
                    'capacity' => 4,
                    'location' => 'Main Area',
                    'status' => 'available',
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        }
    }
}
