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

        if (!Schema::hasTable('data.data_animals_codes'))
        {
            Schema::create('data.data_animals_codes', function (Blueprint $table)
            {
                $table->comment('Идентификационные номера животных');
                $table->increments('code_id')->comment('Инкремент');
                $table->bigInteger('animal_id')->nullable(false)->comment('Идентификатор животного');
                $table->integer('code_type_id')->nullable(true)->default(null)->comment('вид номера');
                $table->string('code_value', 64)->nullable(true)->default(null)->comment('значение');
                $table->string('code_description', 255)->nullable(true)->default(null)->comment('Описание');
                $table->integer('code_status_id')->nullable(true)->default(null)->comment('вид маркировки животного');
                $table->integer('code_tool_type_id')->nullable(true)->default(null)->comment(
                    'тип средства маркировки животного'
                );
                $table->integer('code_tool_location_id')->nullable(true)->default(null)->comment(
                    'id места нанесения маркировки животного'
                );
                $table->timestamp('code_tool_date_set')->nullable(true)->default(null)->comment(
                    'дата нанесения маркировки животного'
                );
                $table->timestamp('code_tool_date_out')->nullable(true)->default(null)->comment(
                    'дата выбытия маркировки животного'
                );
                $table->string('code_tool_photo', 255)->nullable(true)->default(null)->comment(
                    'фото средства маркирования'
                );
                $table->addColumn('system.system_status_delete','code_status_delete')->nullable(false)->default('active')->comment('флаг удаления записи');
                $table->timestamp('created_at')->nullable(false)->default(DB::raw('CURRENT_TIMESTAMP'))->comment(
                    'Дата создания записи'
                );
                $table->timestamp('updated_at')->nullable(false)->default(DB::raw('CURRENT_TIMESTAMP'))->comment(
                    'Дата обновления записи'
                );
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data.data_animals_codes');
    }
};
