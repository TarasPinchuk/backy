<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Company;

class CompanySeeder extends Seeder
{
    public function run()
    {
        Company::create([
            'name'     => 'Благотворительный Фонд «Помощь»',
            'email'    => 'help_fund@example.com',
            'password' => Hash::make('password'),
        ]);

        Company::create([
            'name'     => 'Центр Добрых Дел',
            'email'    => 'good_center@example.com',
            'password' => Hash::make('password'),
        ]);
    }
}
