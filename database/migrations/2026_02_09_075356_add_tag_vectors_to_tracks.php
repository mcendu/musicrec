<?php

use Illuminate\Database\Migrations\Migration;
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
        // set up pgvector, we need it for the new column.
        //
        // note that this may fail if the database user is not a superuser
        // (which should be the case if you follow common sense); if the
        // error says "insufficient privilege", you should run the following
        // line yourself as the superuser (example for Linux):
        //
        // $ sudo -u postgres psql -c "CREATE EXTENSION IF NOT EXISTS vector;"
        DB::statement('CREATE EXTENSION IF NOT EXISTS vector');

        // add the actual column.
        Schema::table('tracks', function (Blueprint $table) {
            // Laravel don't know how to emit DDL for the halfvec type.
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
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // normal drop column operation.
        Schema::table('tracks', function (Blueprint $table) {
            $table->dropColumn('tags');
        });

        // since we set up pgvector when migrating up, we should tear it
        // down here. again it may fail if the database user is not a
        // superuser; see above and you should know what to do.
        DB::statement('DROP EXTENSION vector');
    }
};
