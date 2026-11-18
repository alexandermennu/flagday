<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Whether the Honors Ball RSVP is accepting responses — opened and closed from the admin
 * dashboard. Starts open, so deploying this changes nothing until an admin closes it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rsvp_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_open')->default(true);
            $table->timestamps();
        });

        DB::table('rsvp_settings')->insert(['is_open' => true, 'created_at' => now(), 'updated_at' => now()]);
    }

    public function down(): void
    {
        Schema::dropIfExists('rsvp_settings');
    }
};
