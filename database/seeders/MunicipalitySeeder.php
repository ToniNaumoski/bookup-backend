<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\City;
use App\Models\Municipality;

class MunicipalitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $skopje = City::where('name', 'Скопје')->first();

        if ($skopje) {
            $municipalities = [
                'Скопје Центар',
                'Карпош',
                'Кисела Вода',
                'Аеродром',
                'Гази Баба',
                'Бутел',
                'Чаир',
                'Ѓорче Петров',
                'Сарај',
                'Шуто Оризари',
                'Сопиште',
                'Студеничани',
                'Зелениково',
                'Петровец',
                'Илинден',
                'Арачиново',
                'Чучер Сандево',
                'Автокоманда'
            ];

            foreach ($municipalities as $municipality) {
                Municipality::create([
                    'name' => $municipality,
                    'city_id' => $skopje->id
                ]);
            }
        }
    }
}
