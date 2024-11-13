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

        if (!Schema::hasTable('data.data_users_participations'))
        {
            Schema::create('data.data_users_participations', function (Blueprint $table)
            {
                $table->comment('Связка пользователя с хозяйствами/регионами/районами');
                $table->increments('participation_id')->comment('Инкремент');
                $table->integer('user_id')->nullable(false)->comment('ID пользователя в таблице SYSTEM.SYSTEM_USERS');
                $table->addColumn('system.system_participations_types', 'participation_item_type')->nullable(false)->default('company')->comment('Тип привязки (компания/регион/район)');
                $table->integer('participation_item_id')->nullable(true)->default(null)->comment(
                    'ID привязки (company_location_id/region_id/district_id)'
                );
                $table->integer('role_id')->nullable(false)->comment('ID роли в таблице SYSTEM.SYSTEM_ROLES');
                $table->addColumn('system.system_status', 'participation_status')->nullable(false)->default('enabled')->comment('Статус связки');
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
        Schema::dropIfExists('data.data_users_participations');
    }
};
