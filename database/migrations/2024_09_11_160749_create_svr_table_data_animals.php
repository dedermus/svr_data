<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Svr\Core\Traits\PostgresGrammar;

return new class extends Migration
{
    use PostgresGrammar;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->enumExists();

        if (!Schema::hasTable('data.data_animals'))
        {
            Schema::create('data.data_animals', function (Blueprint $table)
            {
                $table->comment('Данные по животным');
                $table->increments('animal_id')->comment('Инкремент');
                $table->integer('company_location_id')->index()->nullable(false)->comment(
                    'COMPANY_LOCATION_ID локации животного в таблице DATA.DATA_COMPANIES_LOCATIONS'
                );
                $table->integer('polovoz_id')->nullable(true)->default(null)->comment(
                    'ID половозрастной группы животного'
                );
                $table->integer('breed_id')->index()->nullable(true)->default(null)->comment(
                    'BREED_ID породы животного в таблице DIRECTORIES.ANIMALS_BREEDS'
                );
                $table->smallInteger('animal_task')->nullable(true)->default(null)->comment(
                    'код задачи берется из таблицы TASKS.NTASK (1 – молоко / 6- мясо / 4 - овцы)'
                );
                $table->string('animal_guid_self', 64)->index()->nullable(true)->default(null)->comment(
                    'гуид животного, который генерирует СВР в момент создания RAW записи'
                );
                $table->string('animal_guid_horriot', 64)->index()->nullable(true)->default(null)->comment(
                    'гуид уникального регистрационного номера из Хорриот'
                );
                $table->string('animal_number_horriot', 64)->index()->nullable(true)->default(null)->comment(
                    'гуид уникального регистрационного номера их Хорриот'
                );
                $table->string('animal_nanimal', 128)->nullable(true)->default(null)
                    ->comment('животное - НЕ!!! уникальный идентификатор'
                );
                $table->string('animal_nanimal_time', 128)->nullable(true)->default(null)
                    ->comment('животное - уникальный идентификатор (наверное...)'
                );
                $table->string('animal_code_inv_value', 32)->index()->nullable(true)->default(null)
                    ->comment('Значение инвентарного номера животного');
                $table->string('animal_code_rshn_value', 32)->index()->nullable(true)->default(null)
                    ->comment('Значение РСХН (УНСМ) номера животного');
                $table->integer('animal_code_chip_id')->index()->nullable(true)->default(true)->comment(
                    'CODE_ID чипа животного  в таблице DATA.DATA_ANIMALS_CODES'
                );
                $table->integer('animal_code_left_id')->index()->nullable(true)->default(true)->comment(
                    'CODE_ID левого номера животного в таблице DATA.DATA_ANIMALS_CODES'
                );
                $table->integer('animal_code_right_id')->index()->nullable(true)->default(true)->comment(
                    'CODE_ID правого номера животного в таблице DATA.DATA_ANIMALS_CODES'
                );
                $table->integer('animal_code_rshn_id')->index()->nullable(true)->default(true)->comment(
                    'CODE_ID номера РСХН в таблице DATA.DATA_ANIMALS_CODES'
                );
                $table->integer('animal_code_inv_id')->index()->nullable(true)->default(true)->comment(
                    'CODE_ID инвентарного номера животного в таблице DATA.DATA_ANIMALS_CODES'
                );
                $table->integer('animal_code_device_id')->index()->nullable(true)->default(true)->comment(
                    'CODE_ID номера в оборудовании животного в таблице DATA.DATA_ANIMALS_CODES'
                );
                $table->integer('animal_code_tattoo_id')->index()->nullable(true)->default(true)->comment(
                    'CODE_ID тату животного в таблице DATA.DATA_ANIMALS_CODES'
                );
                $table->integer('animal_code_import_id')->index()->nullable(true)->default(true)->comment(
                    'CODE_ID импортного номера животного в таблице DATA.DATA_ANIMALS_CODES'
                );
                $table->integer('animal_code_name_id')->index()->nullable(true)->default(true)->comment(
                    'CODE_ID клички животного в таблице DATA.DATA_ANIMALS_CODES'
                );
                $table->timestamp('animal_date_create_record')->nullable(true)->default(null)->comment(
                    'Дата создания записи в формате YYYY-mm-dd'
                );
                $table->timestamp('animal_date_birth')->nullable(true)->default(null)->comment(
                    'дата рождения животного в формате YYYY-mm-dd'
                );
                $table->timestamp('animal_date_import')->nullable(true)->default(null)->comment(
                    'дата ввоза животного в формате YYYY-mm-dd'
                );
                $table->timestamp('animal_date_income')->nullable(true)->default(null)->comment(
                    'дата поступления животного в формате YYYY-mm-dd'
                );
                $table->integer('animal_sex_id')->index()->nullable(true)->default(null)->comment(
                    'GENDER_ID пол животного в таблице DIRECTORIES.GENDERS'
                );
                $table->addColumn('system.system_sex', 'animal_sex')->nullable(true)->default(null)->comment(
                    'пол животного enum'
                );
                $table->addColumn('system.system_breeding_value', 'animal_breeding_value')->nullable(false)->default(
                    'UNDEFINED'
                )->comment('племенная ценность животного');
                $table->string('animal_colour', 100)->nullable(true)->default(null)->comment('масть (окрас) животного');
                $table->integer('animal_place_of_keeping_id')->index()->nullable(true)->default(null)->comment(
                    'COMPANY_ID места содержания животного'
                );
                $table->integer('animal_object_of_keeping_id')->index()->nullable(true)->default(null)->comment(
                    'company_object_id места содержания животного в таблице data.data_companies_objects'
                );
                $table->integer('animal_place_of_birth_id')->index()->nullable(true)->default(null)->comment(
                    'COMPANY_ID места рождения животного в таблице DATA.DATA_COMPANIES'
                );
                $table->integer('animal_object_of_birth_id')->index()->nullable(true)->default(null)->comment(
                    'company_object_id места рождения животного в таблице  в таблице data.data_companies_objects'
                );
                $table->integer('animal_type_of_keeping_id')->index()->nullable(true)->default(null)->comment(
                    'KEEPING_TYPE_ID типа содержания животного в таблице DIRECTORIES.KEEPING_TYPES'
                );
                $table->integer('animal_purpose_of_keeping_id')->index()->nullable(true)->default(null)->comment(
                    'KEEPING_PURPOSE_ID цели содержания животного в таблице DIRECTORIES.KEEPING_PURPOSES'
                );
                $table->integer('animal_country_nameport_id')->index()->nullable(true)->default(null)->comment(
                    'COUNTRY_ID страны ввоза животного в таблице DIRECTORIES.COUNTRIES'
                );
                $table->string('animal_description', 100)->nullable(true)->default(null)->comment('описание животного');
                $table->string('animal_photo', 255)->nullable(true)->default(null)->comment('фото животного');
                $table->timestamp('animal_out_date')->nullable(true)->default(null)->comment(
                    'дата выбытия животного в формате YYYY-mm-dd'
                );
                $table->string('animal_out_reason', 255)->nullable(true)->default(null)->comment(
                    'причина выбытия животного'
                );
                $table->string('animal_out_rashod', 255)->nullable(true)->default(null)->comment('расход животного');
                $table->integer('animal_out_type_id')->index()->nullable(true)->default(null)->comment(
                    'OUT_TYPE_ID типа выбытия животного в таблице DIRECTORIES.OUT_TYPES '
                );
                $table->integer('animal_out_basis_id')->index()->nullable(true)->default(null)->comment(
                    'OUT_BASIS_ID основания выбытия животного в таблице DIRECTORIES.OUT_BASISES'
                );
                $table->integer('animal_out_weight')->nullable(true)->default(null)->comment(
                    'живая масса (кг) животного при выбытии'
                );
                $table->string('animal_mother_num', 64)->nullable(true)->default(null)->comment(
                    'уникальный номер матери животного'
                );
                $table->string('animal_mother_rshn', 64)->nullable(true)->default(null)->comment(
                    'рсхн номер матери животного'
                );
                $table->string('animal_mother_inv', 64)->nullable(true)->default(null)->comment(
                    'инвентарный номер матери животного'
                );
                $table->timestamp('animal_mother_date_birth')->nullable(true)->default(null)
                    ->comment('дата рождения матери в формате YYYY-mm-dd'
                );
                $table->integer('animal_mother_breed_id')->index()->nullable(true)->default(null)
                    ->comment('BREED_ID породы матери в таблице DIRECTORIES.ANIMALS_BREEDS'
                );
                $table->string('animal_father_num', 64)->nullable(true)->default(null)
                    ->comment('уникальный номер отца животного'
                );
                $table->string('animal_father_rshn', 64)->nullable(true)->default(null)
                    ->comment('рсхн номер отца животного'
                );
                $table->string('animal_father_inv', 64)->nullable(true)->default(null)
                    ->comment('инвентарный номер отца животного'
                );
                $table->timestamp('animal_father_date_birth')->nullable(true)->default(null)
                    ->comment('дата рождения отца в формате YYYY-mm-dd'
                );
                $table->integer('animal_father_breed_id')->index()->nullable(true)->default(null)
                    ->comment('BREED_ID породы отца в таблице DIRECTORIES.ANIMALS_BREEDS'
                );
                $table->addColumn('system.system_status', 'animal_status')->nullable(false)->default('enabled')
                    ->comment('статус животного'
                );
                $table->addColumn('system.system_status_delete', 'animal_status_delete')->nullable(false)->default('active')
                    ->comment('статус удаления животного'
                );
                $table->addColumn('system.system_status', 'animal_repair_status')->nullable(false)->default('disabled')
                    ->comment('Флаг починки животного'
                );
                $table->timestamp('created_at')->nullable(false)->default(DB::raw('CURRENT_TIMESTAMP'))
                    ->comment('дата создания животного'
                );
                $table->timestamp('updated_at')->nullable(false)->default(DB::raw('CURRENT_TIMESTAMP'))
                    ->comment('дата обновления животного'
                );
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data.data_animals');
    }
};
