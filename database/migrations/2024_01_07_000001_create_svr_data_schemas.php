<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

use Svr\Core\Traits\PostgresGrammar;

return new class extends Migration {

    use PostgresGrammar;
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->enumExists();

        DB::statement('CREATE SCHEMA IF NOT EXISTS data');
        DB::statement("COMMENT ON SCHEMA data IS 'Основная схема'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP SCHEMA IF EXISTS data CASCADE');
    }
};
