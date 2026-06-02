<?php

namespace Database\Seeders;

use App\Models\PricingType;
use Illuminate\Database\Seeder;

class PricingTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['code' => 'daily', 'name' => 'Daily'],
            ['code' => 'weekly', 'name' => 'Weekly'],
            ['code' => 'monthly', 'name' => 'Monthly'],
            ['code' => 'yearly', 'name' => 'Yearly'],
        ];

        foreach ($types as $type) {
            PricingType::firstOrCreate(['code' => $type['code']], $type);
        }
    }
}