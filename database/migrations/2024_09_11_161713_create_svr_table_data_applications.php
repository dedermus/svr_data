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

        if (!Schema::hasTable('data.data_applications'))
        {
            Schema::create('data.data_applications', function (Blueprint $table)
            {
                $table->comment('Заявки на регистрацию');
                $table->increments('application_id')->comment('Инкремент');
                $table->integer('company_location_id')->nullable(false)->comment('Идентификатор локации компании');
                $table->integer('user_id')->nullable(false)->comment('Идентификатор пользователя создавшего заявку');
                $table->integer('doctor_id')->nullable(true)->default(null)->comment(
                    'Идентификатор ветврача отправившего заявку'
                );
                $table->timestamp('application_date_create')->nullable(false)->default(DB::raw('CURRENT_TIMESTAMP'))
                    ->comment('Дата создания заявки');
                $table->timestamp('application_date_horriot')->nullable(true)->default(null)->comment(
                    'Дата отправки заявки в хорриот'
                );
                $table->timestamp('application_date_complete')->nullable(true)->default(null)->comment(
                    'Дата закрытия заявки'
                );
                $table->addColumn('system.application_status', 'application_status')->index()->nullable(false)->default('created')
                    ->comment("статус заявки ('created', 'prepared', 'sent', 'complete_full', 'complete_partial', 'finished') "
                    );
                $table->timestamp('created_at')->nullable(false)->default(DB::raw('CURRENT_TIMESTAMP'))
                    ->comment('Дата создания записи'
                );
                $table->timestamp('updated_at')->index()->nullable(false)->default(DB::raw('CURRENT_TIMESTAMP'))
                    ->comment('Дата удаления записи'
                );
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data.data_applications');
    }
};
