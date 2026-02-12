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
        // Create the table proper.
        Schema::create('artists', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        // Sync artist names from the tracks table.
        DB::table('tracks')->orderBy('id')
            ->chunk(128, function (Collection $tracks) {
                $time = date_create_immutable()->format(DATE_ATOM);
                foreach ($tracks as $track) {
                    DB::table('artists')->insertOrIgnore([
                        'name' => $track->artist,
                        'created_at' => $time,
                        'updated_at' => $time,
                    ]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('artists', function (Blueprint $table) {
            $table->dropUnique('artists_name_unique');
        });
        Schema::dropIfExists('artists');
    }
};
