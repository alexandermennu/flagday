<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Links a Calendar of Events entry to the public event page (config/graduation.php slug)
 * it describes, so that page can show the dress code saved on the entry in the admin.
 *
 * Existing entries are linked by title — the first entry (by position) matching each
 * event — so dress codes already saved show up without re-editing anything.
 */
return new class extends Migration
{
    private const TITLE_MATCHES = [
        'honors-ball' => ['%honors ball%', '%honors award%', '%awards night%'],
        'baccalaureate' => ['%baccalaureate%'],
        'convocation' => ['%convocation%'],
    ];

    public function up(): void
    {
        Schema::table('schedule_events', function (Blueprint $table) {
            $table->string('event_slug', 30)->nullable()->unique()->after('title');
        });

        foreach (self::TITLE_MATCHES as $slug => $patterns) {
            $id = DB::table('schedule_events')
                ->whereNull('event_slug')
                ->where(function ($query) use ($patterns) {
                    foreach ($patterns as $pattern) {
                        $query->orWhereRaw('LOWER(title) LIKE ?', [$pattern]);
                    }
                })
                ->orderBy('position')
                ->orderBy('id')
                ->value('id');

            if ($id !== null) {
                DB::table('schedule_events')->where('id', $id)->update(['event_slug' => $slug]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('schedule_events', function (Blueprint $table) {
            $table->dropUnique(['event_slug']);
            $table->dropColumn('event_slug');
        });
    }
};
