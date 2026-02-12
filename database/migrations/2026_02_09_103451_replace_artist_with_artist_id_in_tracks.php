<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // add the artist id column
        Schema::table('tracks', function (Blueprint $table) {
            $table->foreignId('artist_id')->index()->nullable()
                ->constrained();
        });

        // populate
        DB::table('tracks')->chunkById(128, function(Collection $tracks) {
            foreach ($tracks as $track) {
                DB::table('tracks')->where('id', $track->id)
                    ->update([
                        'artist_id' => DB::table('artists')
                            ->where('name', '=', $track->artist)
                            ->first()->id
                    ]);
            }
        });

        Schema::table('tracks', function (Blueprint $table) {
            // drop artist name
            $table->dropUnique('tracks_name_artist_unique');
            $table->dropIndex('tracks_artist_index');
            $table->dropColumn('artist');

            // make artist id non-nullable
            $table->foreignId('artist_id')->change();
            // add name-artist unique constraint
            $table->unique(['name', 'artist_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // add the artist name column back
        Schema::table('tracks', function (Blueprint $table) {
            $table->string('artist')->index()->default('');
        });

        // populate
        DB::table('tracks')->chunkById(128, function(Collection $tracks) {
            foreach ($tracks as $track) {
                DB::table('tracks')->where('id', $track->id)
                    ->update([
                        'artist' => DB::table('artists')
                            ->where('id', '=', $track->artist_id)
                            ->first()->name
                    ]);
            }
        });

        // drop artist id
        Schema::table('tracks', function (Blueprint $table) {
            $table->dropUnique('tracks_name_artist_id_unique');
            $table->dropIndex('tracks_artist_id_index');
            $table->dropColumn('artist_id');

            $table->unique(['name', 'artist']);
        });
    }
};
