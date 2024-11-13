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

        if (!Schema::hasTable('data.data_companies'))
        {
            Schema::create('data.data_companies', function (Blueprint $table)
            {
                $table->comment('Список хозяйств');
                $table->increments('company_id')->comment('Инкремент');
                $table->string('company_base_index', 7)->nullable(true)->default(null)->comment(
                    'Базовый индекс хозяйства'
                );
                $table->string('company_guid_vetis', 128)->nullable(true)->default(null)->comment(
                    'Уникальный номер поднадзорного объекта, который есть в ВЕТИС'
                );
                $table->string('company_guid', 36)->unique()->nullable(true)->default(null)->comment('UUID4');
                $table->string('company_name_short', 100)->nullable(true)->default(null)->comment(
                    'Название хозяйства - короткое'
                );
                $table->string('company_name_full', 255)->nullable(true)->default(null)->comment(
                    'Название хозяйства - полное'
                );
                $table->string('company_address', 255)->nullable(true)->default(null)->comment('Адрес хозяйства');
                $table->string('company_inn', 12)->nullable(true)->default(null)->comment(
                    'ИНН - индивидуальный налоговый номер'
                );
                $table->string('company_kpp', 20)->nullable(true)->default(null)->comment(
                    'КПП - код причины постановки на учет'
                );
                $table->addColumn('system.system_status', 'company_status')->nullable(false)->default('enabled')->comment('Статус записи (enabled - активна/disabled - не активна)');
                $table->addColumn('system.system_status', 'company_status_horriot')->nullable(false)->default('disabled')->comment('Статус первоначального нахождения данных о хозяйстве в хорриот');
                $table->addColumn('system.system_status_delete', 'company_status_delete')->nullable(false)->default('active')->comment('Статус псевдо-удаленности записи (active - не удалена/deleted - удалена)');
                $table->timestamp('company_date_update_objects')->nullable(false)->default('2024-08-28 09:00:00')
                    ->comment('Дата последнего обновления поднадзорных объектов компании');
                $table->integer('company_objects_offset')->nullable(true)->default(0)->comment('offset для обновления ПО');
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
        Schema::dropIfExists('data.data_companies');
    }
};
