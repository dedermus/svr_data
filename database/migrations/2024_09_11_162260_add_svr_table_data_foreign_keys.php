<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Svr\Core\Traits\PostgresGrammar;

return new class extends Migration
{
    use PostgresGrammar;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('data.data_animals', function (Blueprint $table)
        {
            $table->foreign('company_location_id')->references('company_location_id')->on('data.data_companies_locations')->cascadeOnUpdate()->noActionOnDelete();
            $table->foreign('breed_id')->references('breed_id')->on('directories.animals_breeds')->cascadeOnUpdate()->noActionOnDelete();
            $table->foreign('animal_code_chip_id')->references('code_id')->on('data.data_animals_codes')->cascadeOnUpdate()->noActionOnDelete();
            $table->foreign('animal_code_left_id')->references('code_id')->on('data.data_animals_codes')->cascadeOnUpdate()->noActionOnDelete();
            $table->foreign('animal_code_right_id')->references('code_id')->on('data.data_animals_codes')->cascadeOnUpdate()->noActionOnDelete();
            $table->foreign('animal_code_rshn_id')->references('code_id')->on('data.data_animals_codes')->cascadeOnUpdate()->noActionOnDelete();
            $table->foreign('animal_code_inv_id')->references('code_id')->on('data.data_animals_codes')->cascadeOnUpdate()->noActionOnDelete();
            $table->foreign('animal_code_device_id')->references('code_id')->on('data.data_animals_codes')->cascadeOnUpdate()->noActionOnDelete();
            $table->foreign('animal_code_tattoo_id')->references('code_id')->on('data.data_animals_codes')->cascadeOnUpdate()->noActionOnDelete();
            $table->foreign('animal_code_import_id')->references('code_id')->on('data.data_animals_codes')->cascadeOnUpdate()->noActionOnDelete();
            $table->foreign('animal_code_name_id')->references('code_id')->on('data.data_animals_codes')->cascadeOnUpdate()->noActionOnDelete();
            $table->foreign('animal_sex_id')->references('gender_id')->on('directories.genders')->cascadeOnUpdate()->noActionOnDelete();
            $table->foreign('animal_place_of_keeping_id')->references('company_id')->on('data.data_companies')->cascadeOnUpdate()->noActionOnDelete();
            $table->foreign('animal_object_of_keeping_id')->references('company_object_id')->on('data.data_companies_objects')->cascadeOnUpdate()->noActionOnDelete();
            $table->foreign('animal_place_of_birth_id')->references('company_id')->on('data.data_companies')->cascadeOnUpdate()->noActionOnDelete();
            $table->foreign('animal_object_of_birth_id')->references('company_object_id')->on('data.data_companies_objects')->cascadeOnUpdate()->noActionOnDelete();
            $table->foreign('animal_type_of_keeping_id')->references('keeping_type_id')->on('directories.keeping_types')->cascadeOnUpdate()->noActionOnDelete();
            $table->foreign('animal_purpose_of_keeping_id')->references('keeping_purpose_id')->on('directories.keeping_purposes')->cascadeOnUpdate()->noActionOnDelete();
            $table->foreign('animal_country_nameport_id')->references('country_id')->on('directories.countries')->cascadeOnUpdate()->noActionOnDelete();
            $table->foreign('animal_out_type_id')->references('out_type_id')->on('directories.out_types')->cascadeOnUpdate()->noActionOnDelete();
            $table->foreign('animal_out_basis_id')->references('out_basis_id')->on('directories.out_basises')->cascadeOnUpdate()->noActionOnDelete();
            $table->foreign('animal_mother_breed_id')->references('breed_id')->on('directories.animals_breeds')->cascadeOnUpdate()->noActionOnDelete();
            $table->foreign('animal_father_breed_id')->references('breed_id')->on('directories.animals_breeds')->cascadeOnUpdate()->noActionOnDelete();
        });

        Schema::table('data.data_animals_codes', function (Blueprint $table)
        {
            $table->foreign('animal_id')->references('animal_id')->on('data.data_animals')->cascadeOnUpdate()->noActionOnDelete();
            $table->foreign('code_type_id')->references('mark_type_id')->on('directories.mark_types')->cascadeOnUpdate()->noActionOnDelete();
            $table->foreign('code_status_id')->references('mark_status_id')->on('directories.mark_statuses')->cascadeOnUpdate()->noActionOnDelete();
            $table->foreign('code_tool_type_id')->references('mark_tool_type_id')->on('directories.mark_tool_types')->cascadeOnUpdate()->noActionOnDelete();
            $table->foreign('code_tool_location_id')->references('tool_location_id')->on('directories.tools_locations')->cascadeOnUpdate()->noActionOnDelete();
        });

        Schema::table('data.data_applications', function (Blueprint $table)
        {
            $table->foreign('company_location_id')->references('company_location_id')->on('data.data_companies_locations')->cascadeOnUpdate()->noActionOnDelete();
            $table->foreign('user_id')->references('user_id')->on('system.system_users')->cascadeOnUpdate()->noActionOnDelete();
        });

        Schema::table('data.data_applications_animals', function (Blueprint $table)
        {
            $table->foreign('application_id')->references('application_id')->on('data.data_applications')->cascadeOnUpdate()->noActionOnDelete();
            $table->foreign('animal_id')->references('animal_id')->on('data.data_animals')->cascadeOnUpdate()->noActionOnDelete();
        });

        Schema::table('data.data_companies_locations', function (Blueprint $table)
        {
            $table->foreign('company_id')->references('company_id')->on('data.data_companies')->cascadeOnUpdate()->noActionOnDelete();
            $table->foreign('region_id')->references('region_id')->on('directories.countries_regions')->cascadeOnUpdate()->noActionOnDelete();
            $table->foreign('district_id')->references('district_id')->on('directories.countries_regions_districts')->cascadeOnUpdate()->noActionOnDelete();
        });

        Schema::table('data.data_companies_objects', function (Blueprint $table)
        {
            $table->foreign('company_id')->references('company_id')->on('data.data_companies')->cascadeOnUpdate()->noActionOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data.data_users_participations');
    }
};
