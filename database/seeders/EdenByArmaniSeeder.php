<?php

namespace Database\Seeders;

use App\Models\Apartment;
use App\Models\Project;
use Illuminate\Database\Seeder;

class EdenByArmaniSeeder extends Seeder
{
    /**
     * Seeds the EDEN by Armani project and its 60 residential apartments
     * (floors 4-13, units A-F) from the official price list.
     */
    public function run(): void
    {
        $project = Project::updateOrCreate(
            ['name' => 'EDEN by Armani'],
            [
                'slug' => 'eden-by-armani',
                'location' => 'Dhaka, Bangladesh',
                'description' => 'Residential apartment project — floors 4 to 13, units A-F per floor.',
                'base_price' => 8500000,
                'status' => 'active',
            ],
        );

        // North-facing per-sqft rate for each floor; south-facing units are +500 BDT/sqft.
        $northRateByFloor = [
            4 => 6800, 5 => 7000, 6 => 7000, 7 => 7000,
            8 => 7200, 9 => 7200, 10 => 7400, 11 => 7400,
            12 => 7400, 13 => 6800,
        ];

        // Unit letter => [size sqft, facing]
        $units = [
            'A' => [2218, 'North'],
            'B' => [2218, 'South'],
            'C' => [1710, 'North'],
            'D' => [1710, 'South'],
            'E' => [1250, 'North'],
            'F' => [1250, 'South'],
        ];

        $carParking = 800000;
        $bikeParking = 150000;
        $utility = 1000000;

        foreach ($northRateByFloor as $floor => $northRate) {
            foreach ($units as $letter => [$size, $facing]) {
                $rate = $facing === 'South' ? $northRate + 500 : $northRate;
                $price = $size * $rate;

                Apartment::updateOrCreate(
                    [
                        'project_id' => $project->id,
                        'apartment_number' => "{$floor}-{$letter}",
                    ],
                    [
                        'floor_label' => $this->ordinal($floor) . ' Floor',
                        'floor_number' => $floor,
                        'size_sqft' => $size,
                        'facing' => $facing,
                        'per_sqft_rate' => $rate,
                        'apartment_price' => $price,
                        'car_parking_cost' => $carParking,
                        'bike_parking_cost' => $bikeParking,
                        'utility_connection_cost' => $utility,
                        'gross_total' => $price + $carParking + $bikeParking + $utility,
                    ],
                );
            }
        }
    }

    private function ordinal(int $n): string
    {
        $suffix = match (true) {
            $n % 100 >= 11 && $n % 100 <= 13 => 'th',
            $n % 10 === 1 => 'st',
            $n % 10 === 2 => 'nd',
            $n % 10 === 3 => 'rd',
            default => 'th',
        };

        return $n . $suffix;
    }
}
