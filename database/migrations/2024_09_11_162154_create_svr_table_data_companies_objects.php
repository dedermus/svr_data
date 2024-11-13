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

        if (!Schema::hasTable('data.data_companies_objects'))
        {
            Schema::create('data.data_companies_objects', function (Blueprint $table)
            {
                $table->comment('Поднадзорные объекты компаний');
                $table->increments('company_object_id')->comment('Инкремент');
                $table->integer('company_id')->nullable(false)->comment('ID компании');
                $table->string('company_object_guid_self', 128)->nullable(false)->comment('GUID объекта');
                $table->string('company_object_guid_horriot', 128)->nullable(false)->comment('GUID объекта в хорриот');
                $table->string('company_object_approval_number', 64)->nullable(false)->comment('Номер');
                $table->string('company_object_address_view', 512)->nullable(false)->comment('Адрес');
                $table->boolean('company_object_is_favorite')->nullable(false)->default(false)->comment('Избранный ПО');
                $table->timestamp('created_at')->nullable(false)->default(DB::raw('CURRENT_TIMESTAMP'))
                    ->comment('Дата создания записи');
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
        Schema::dropIfExists('data.data_companies_objects');
    }
};
