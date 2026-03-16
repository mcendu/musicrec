<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Grammars\PostgresGrammar;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $grammar = DB::connection()->getQueryGrammar();

        if ($grammar instanceof PostgresGrammar) {
            Schema::table('tracks', function (Blueprint $table) {
                DB::statement('CREATE INDEX tracks_tags_index ON tracks
                    USING hnsw (tags halfvec_cosine_ops);');
            });
        }
        // TODO: Some other database software, like MariaDB, also have
        // support for vectors. Figure out how to create indices for them.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $grammar = DB::connection()->getQueryGrammar();
        if (!($grammar instanceof PostgresGrammar)) return;

        Schema::table('tracks', function (Blueprint $table) {
            $table->dropIndex('tracks_tags_index');
        });
    }
};
