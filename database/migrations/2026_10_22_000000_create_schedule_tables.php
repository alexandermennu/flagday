<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The Protocols & Calendar page content, managed in admin → Protocols & Calendar.
 *
 * Seeded once from the university's graduation program ("Protocols" and "Calendar of
 * Events"). That source is the Class of 2025 program, so the settings row starts
 * unconfirmed and the public page flags every date as a 2025 date to be confirmed.
 * Wording is kept exactly as in the source.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedule_settings', function (Blueprint $table) {
            $table->id();
            $table->string('theme', 160)->nullable();
            $table->boolean('is_confirmed')->default(false);
            $table->string('source_year', 4)->nullable();
            $table->timestamps();
        });

        Schema::create('schedule_protocols', function (Blueprint $table) {
            $table->id();
            $table->string('time_label', 60);
            $table->text('description');
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::create('schedule_events', function (Blueprint $table) {
            $table->id();
            $table->string('title', 120);
            $table->date('event_date')->nullable();
            $table->string('start_time', 5)->nullable();
            $table->string('end_time', 5)->nullable();
            $table->string('time_note', 60)->nullable();
            $table->string('venue', 160)->nullable();
            $table->string('theme', 160)->nullable();
            $table->string('speaker_label', 40)->nullable();
            $table->string('speaker', 160)->nullable();
            $table->string('guest_of_honor', 160)->nullable();
            $table->string('attendance', 160)->nullable();
            $table->string('dress_code_label', 40)->nullable();
            $table->string('dress_code', 120)->nullable();
            $table->text('description')->nullable();
            $table->json('notes')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        $now = now();

        DB::table('schedule_settings')->insert([
            'theme' => 'Rooted in Excellence, Rising to Global Challenges',
            'is_confirmed' => false,
            'source_year' => '2025',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $protocols = [
            ['From 8:00 a.m.', 'The students arrive at the Monrovia Christian Fellowship premise and assemble at the Reception Hall as per their listing to be robed for the academic procession.'],
            ['From 8:30 a.m.', 'The University Faculty arrive at Monrovia Christian Fellowship premise and assemble in the church guest room to robe.'],
            ['From 9:00 a.m.', 'Other Guests arrive at the Monrovia Christian Fellowship premise. Those who do not form part of the procession will be seated by the ushers.'],
            ['At 9:30 a.m.', 'the Board of Governors and special Guests arrive at Monrovia Christian Fellowship premise.'],
            ['At 10:15 a.m.', 'the Guest Speaker will arrive at the Monrovia Christian Fellowship premise.'],
            ['At 10:30 a.m.', 'The Academic Procession will commence.'],
        ];
        foreach ($protocols as $i => [$time, $text]) {
            DB::table('schedule_protocols')->insert([
                'time_label' => $time, 'description' => $text, 'position' => $i + 1, 'is_published' => true,
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        $base = [
            'end_time' => null, 'time_note' => null, 'venue' => null, 'theme' => null, 'speaker_label' => null, 'speaker' => null,
            'guest_of_honor' => null, 'attendance' => null, 'dress_code_label' => null, 'dress_code' => null, 'description' => null,
            'notes' => json_encode([]), 'is_published' => true, 'created_at' => $now, 'updated_at' => $now,
        ];
        $events = [
            ['title' => 'Commencement Rehearsal', 'event_date' => '2025-11-14', 'start_time' => '15:30', 'venue' => 'New Campus, Barrolle Practice Ground Road', 'attendance' => 'Attendance Mandatory'],
            ['title' => 'Awards Night', 'event_date' => '2025-11-22', 'start_time' => '19:30', 'time_note' => 'Prompt', 'venue' => 'Shatim Event Center, Duport Road'],
            ['title' => 'Baccalaureate Service', 'event_date' => '2025-11-23', 'start_time' => '14:00', 'venue' => 'Monrovia Christian Fellowship', 'speaker_label' => 'Speaker', 'speaker' => 'Pastor Francis T. Neufville'],
            [
                'title' => 'Commencement Convocation 2025', 'event_date' => '2025-11-25', 'start_time' => '10:00', 'venue' => 'Monrovia Christian Fellowship',
                'speaker_label' => 'Guest Speaker', 'speaker' => 'Hon. Christine N. Umutoni', 'dress_code_label' => 'Color', 'dress_code' => 'Red & White',
                'notes' => json_encode([
                    'Seniors report:',
                    'All guests must be in the Main Hall by 9:00 a.m. as doors will close at this time. Guests cannot hold seats and must be in their seats before the procession begins at 10:00 a.m. prompt.',
                ]),
            ],
        ];
        foreach ($events as $i => $event) {
            DB::table('schedule_events')->insert(array_merge($base, $event, ['position' => $i + 1]));
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_events');
        Schema::dropIfExists('schedule_protocols');
        Schema::dropIfExists('schedule_settings');
    }
};
