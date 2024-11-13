<?php

namespace Svr\Data\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Svr\Data\Seeders;

class DataSeeder extends Seeder
{
    public function run()
    {
        DB::table('data.data_animals_codes')->truncate();
        DB::table('data.data_animals')->truncate();
        DB::table('data.data_applications_animals')->truncate();
        DB::table('data.data_applications')->truncate();
        DB::table('data.data_companies_locations')->truncate();
        DB::table('data.data_companies_objects')->truncate();
        DB::table('data.data_companies')->truncate();

        (new Seeders\DataCompaniesSeeder())->run();
        (new Seeders\DataCompaniesLocationsSeeder())->run();
        (new Seeders\DataCompaniesObjectsSeeder())->run();
        (new Seeders\DataAnimalsSeeder())->run();
        (new Seeders\DataAnimalsCodesSeeder())->run();
        (new Seeders\DataApplicationsSeeder())->run();
        (new Seeders\DataApplicationsAnimalsSeeder())->run();
    }
}
