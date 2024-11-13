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

        if (!Schema::hasTable('data.data_applications_animals'))
        {
            Schema::create('data.data_applications_animals', function (Blueprint $table)
            {
                $table->comment('Животные в заявке');
                $table->increments('application_animal_id')->comment('Инкремент');
                $table->integer('application_id')->nullable(false)->default(null)->comment('id заявки');
                $table->integer('animal_id')->nullable(false)->default(null)->comment('id животного');
                $table->timestamp('application_animal_date_add')->nullable(false)->default(DB::raw('CURRENT_TIMESTAMP'))->comment('дата добавления');
                $table->timestamp('application_animal_date_sent')->nullable(true)->default(null)->comment('Дата нажатия кнопки отправки животного на регистрацию');
                $table->timestamp('application_animal_date_horriot')->nullable(true)->default(null)->comment('дата отправки в хорриот');
                $table->timestamp('application_animal_date_response')->nullable(true)->default(null)->comment('дата получения ответа от хорриот');
                $table->addColumn('system.application_animal_status', 'application_animal_status')->index()->nullable(false)->default('added')->comment("статус животного ('added', 'deleted', 'sent', 'registered', 'rejected', 'finished'");
                $table->timestamp('application_animal_date_last_update')->index()->nullable(false)->default(DB::raw('CURRENT_TIMESTAMP'))->comment('Дата последнего запроса к хорриоту');
                $table->addColumn('system.herriot_error_types', 'application_response_herriot_error_type')->nullable(false)->default('unknown')->comment("статус заявки ('created', 'prepared', 'sent', 'complete_full', 'complete_partial', 'finished')");
                $table->string('application_response_herriot_error_code', 64)->nullable(true)->default(null)->comment('Код ошибки при отправке в Хорриот');
                $table->addColumn('system.herriot_error_types', 'application_response_application_herriot_error_type')->nullable(false)->default('unknown')->comment("статус заявки ('created', 'prepared', 'sent', 'complete_full', 'complete_partial', 'finished')");
                $table->string('application_response_application_herriot_error_code', 64)->nullable(true)->default(null)->comment('Код ошибки при ответе из Хорриот');
                $table->string('application_herriot_application_id', 64)->nullable(true)->default(null)->comment('application_id из хорриота');
                $table->string('application_herriot_send_text_error', 1000)->nullable(true)->default(null)->comment('Текст ошибки при отправке в Хорриот');
                $table->string('application_herriot_check_text_error', 1000)->nullable(true)->default(null)->comment('Текст ошибки при проверке статуса регистрации в Хорриот');
                $table->timestamp('updated_at')->nullable(false)->default(DB::raw('CURRENT_TIMESTAMP'))->comment('дата обновления записи');
                $table->timestamp('created_at')->nullable(false)->default(DB::raw('CURRENT_TIMESTAMP'))->comment('дата создания записи');
            });
        }
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data.data_applications_animals');
    }
};
