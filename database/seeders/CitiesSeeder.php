<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CitiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
 $cities = [
    'Скопје',
    'Битола',
    'Куманово',
    'Прилеп',
    'Тетово',
    'Велес',
    'Охрид',
    'Гостивар',
    'Штип',
    'Струмица',
    'Кавадарци',
    'Кочани',
    'Кичево',
    'Радовиш',
    'Гевгелија',
    'Струга',
    'Неготино',
    'Крива Паланка',
    'Свети Николе',
    'Дебар',
    'Виница',
    'Ресен',
    'Делчево',
    'Берово',
    'Кратово',
    'Пробиштип',
    'Богданци',
    'Демир Капија',
    'Валандово',
    'Македонски Брод',
    'Крушево',
    'Демир Хисар',
    'Пехчево',
];

        foreach ($cities as $city) {
            DB::table('cities')->insert([
                'name' => $city,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
