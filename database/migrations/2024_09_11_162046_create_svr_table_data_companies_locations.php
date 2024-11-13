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

        if (!Schema::hasTable('data.data_companies_locations'))
        {
            Schema::create('data.data_companies_locations', function (Blueprint $table)
            {
                $table->comment('Связка хозяйства с районом и регионом');
                $table->increments('company_location_id')->comment('Инкремент');
                $table->integer('company_id')->nullable(false)->comment('ID хозяйства из таблицы DATA.DATA_COMPANIES');
                $table->integer('region_id')->nullable(false)->comment('ID региона из справочника');
                $table->integer('district_id')->nullable(true)->default(null)->comment('ID района из справочника');
                $table->addColumn('system.system_status', 'location_status')->default('enabled')->comment('Статус записи (активна/не активна)');
                $table->addColumn('system.system_status_delete', 'location_status_delete')->default('active')->comment('Статус псевдо-удаленности записи (активна - не удалена/не активна - удалена)');
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
        Schema::dropIfExists('data.data_companies_locations');
    }
};
