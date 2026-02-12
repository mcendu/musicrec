<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Grammars\PostgresGrammar;
use Illuminate\Database\Query\Grammars\SQLiteGrammar;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $grammar = DB::connection()->getQueryGrammar();

        // Don't apply this migration for SQLite
        if ($grammar instanceof SQLiteGrammar) return;

        // set up pgvector, we need it for the new column.
        //
        // note that this may fail if the database user is not a superuser
        // (which should be the case if you follow common sense); if the
        // error says "insufficient privilege", you should run the following
        // line yourself as the superuser (example for Linux):
        //
        // $ sudo -u postgres psql -c "CREATE EXTENSION IF NOT EXISTS vector;"
        if ($grammar instanceof PostgresGrammar)
            DB::statement('CREATE EXTENSION IF NOT EXISTS vector');

        // add the actual column.
        Schema::table('tracks', function (Blueprint $table) use ($grammar) {
            if ($grammar instanceof PostgresGrammar) {
                // use half precision vectors. Laravel can't (as of time)
                // emit DDL for such usage, so this has to be written as
                // raw SQL.
                DB::statement('ALTER TABLE tracks
                    ADD COLUMN tags halfvec(128) NOT NULL
                    DEFAULT \'[
                        0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
                        0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
                        0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
                        0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
                        0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
                        0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
                        0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
                        0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0
                    ]\'');
            } else {
                // For other databases just use what Laravel has to offer.
                $table->vector('tags', 128)->default('[
                    0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
                    0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
                    0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
                    0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
                    0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
                    0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
                    0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
                    0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0
                ]');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $grammar = DB::connection()->getQueryGrammar();

        if ($grammar instanceof SQLiteGrammar) return;

        // normal drop column operation.
        Schema::table('tracks', function (Blueprint $table) {
            $table->dropColumn('tags');
        });

        // since we set up pgvector when migrating up, we should tear it
        // down here. again it may fail if the database user is not a
        // superuser; see above and you should know what to do.
        if ($grammar instanceof PostgresGrammar)
            DB::statement('DROP EXTENSION vector');
    }
};
