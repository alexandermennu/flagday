<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Which graduation event a program belongs to — decides how its PDF is produced (four
 * generated pages, one generated agenda page, or an uploaded file) and which public
 * event page it is published to. Existing programs were all Honors Ball programs.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->string('type', 30)->default('honors-ball')->after('id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->dropIndex(['type']);
            $table->dropColumn('type');
        });
    }
};
