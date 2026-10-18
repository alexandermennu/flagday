{{--
    Program content form, shared by create and edit. Agenda items and award categories are
    repeatable rows (see resources/js/admin.js): add, remove, and reorder; names are
    re-indexed on every change so the submitted arrays follow the on-screen order.

    The Program Type decides which sections apply: elements marked data-program-types are
    shown only for the listed types (toggled live in admin.js; hidden fields are excluded
    server-side by ProgramRequest, so they never block a save or overwrite saved content).
--}}
@csrf

@php
    $in = 'w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-gold-500 focus:outline-none focus:ring-1 focus:ring-gold-500';
    $label = 'mb-1 block text-sm font-semibold text-slate-700';
    $help = 'mt-1 text-xs text-slate-500';
    $agendaRows = old('agenda', $program->agenda ?? []);
    $awardRows = old('awards', $program->awards ?? []);
    $field = fn (string $name) => old($name, $name === 'event_date' ? $program->event_date?->format('Y-m-d') : $program->{$name});
    $type = old('type', $program->type ?? 'honors-ball');
    // Attributes for an element that applies only to some program types.
    $forTypes = fn (string ...$types) => 'data-program-types="'.implode(' ', $types).'"'.(in_array($type, $types, true) ? '' : ' hidden');
    $pdfMaxKb = \App\Models\Program::effectivePdfUploadMaxKb();
@endphp

@if ($errors->has('pdf'))
    <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
        <p class="font-semibold">The PDF could not be generated</p>
        <p class="mt-1">{{ $errors->first('pdf') }}</p>
    </div>
@endif

@php $fieldErrors = collect($errors->getMessages())->except('pdf')->flatten(); @endphp
@if ($fieldErrors->isNotEmpty())
    <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
        <p class="font-semibold">Please correct the following:</p>
        <ul class="mt-1 list-inside list-disc">
            @foreach ($fieldErrors as $message)
                <li>{{ $message }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="space-y-6">
    {{-- ============ Event ============ --}}
    <section class="rounded-2xl border border-slate-200 bg-white p-6">
        <h2 class="text-base font-bold text-ink">Event details</h2>
        <p class="{{ $help }}">Shown on the cover and the agenda page. Leave date, time or venue blank to print “To be announced”.</p>

        <div class="mt-5 grid gap-5 md:grid-cols-2">
            <div class="md:col-span-2">
                <label for="type" class="{{ $label }}">Program Type <span class="text-red-600">*</span></label>
                <select name="type" id="type" required data-program-type
                        @unless ($program->exists) data-type-reload="{{ route('admin.programs.create') }}" @endunless
                        class="{{ $in }} md:max-w-sm">
                    @foreach (\App\Models\Program::TYPES as $value => $text)
                        <option value="{{ $value }}" @selected($type === $value)>{{ $text }}</option>
                    @endforeach
                </select>
                <p class="{{ $help }}">
                    Honors Ball: the four-page program is generated from this form.
                    Baccalaureate: a one-page agenda is generated from this form.
                    Convocation: upload the finished program PDF.
                    Publishing puts the program on that event's public page.
                </p>
            </div>
            <div>
                <label for="title" class="{{ $label }}">Event title <span class="text-red-600">*</span></label>
                <input type="text" name="title" id="title" required maxlength="80" value="{{ $field('title') }}" class="{{ $in }}">
                <p class="{{ $help }}">Used on the agenda page and in the PDF file name, e.g. “Special Honors Awards”.</p>
            </div>
            <div>
                <label for="class_year" class="{{ $label }}">Class year <span class="text-red-600">*</span></label>
                <input type="text" name="class_year" id="class_year" required maxlength="9" value="{{ $field('class_year') }}" class="{{ $in }}">
                <p class="{{ $help }}">The large “Class of …” year on the cover.</p>
            </div>
            <div>
                <label for="edition_label" class="{{ $label }}">Cover ribbon text</label>
                <input type="text" name="edition_label" id="edition_label" maxlength="60" value="{{ $field('edition_label') }}" class="{{ $in }}">
                <p class="{{ $help }}">The orange ribbon under the year, e.g. “9th Annual Special Honors Awards”.</p>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="event_date" class="{{ $label }}">Date</label>
                    <input type="date" name="event_date" id="event_date" value="{{ $field('event_date') }}" class="{{ $in }}">
                </div>
                <div>
                    <label for="event_time" class="{{ $label }}">Time</label>
                    <input type="time" name="event_time" id="event_time" value="{{ $field('event_time') }}" class="{{ $in }}">
                </div>
            </div>
            <div>
                <label for="venue_name" class="{{ $label }}">Venue</label>
                <input type="text" name="venue_name" id="venue_name" maxlength="80" value="{{ $field('venue_name') }}" class="{{ $in }}">
            </div>
            <div>
                <label for="venue_address" class="{{ $label }}">Venue address</label>
                <input type="text" name="venue_address" id="venue_address" maxlength="120" value="{{ $field('venue_address') }}" class="{{ $in }}">
            </div>
            <div class="md:col-span-2">
                <label for="description" class="{{ $label }}">Event description</label>
                <textarea name="description" id="description" rows="3" maxlength="900" class="{{ $in }}">{{ $field('description') }}</textarea>
                <p class="{{ $help }}">Optional. Printed in the side panel of the agenda page (up to 900 characters).</p>
            </div>
            <div>
                <label for="hosted_by" class="{{ $label }}">Hosted by</label>
                <input type="text" name="hosted_by" id="hosted_by" maxlength="80" value="{{ $field('hosted_by') }}" class="{{ $in }}">
                <p class="{{ $help }}">Printed under the agenda as “Hosted by: …”.</p>
            </div>
        </div>
    </section>

    {{-- ============ Institution & contact ============ --}}
    <section class="rounded-2xl border border-slate-200 bg-white p-6">
        <h2 class="text-base font-bold text-ink">Institution &amp; contact</h2>

        <div class="mt-5 grid gap-5 md:grid-cols-3">
            <div>
                <label for="institution_name" class="{{ $label }}">Institution name <span class="text-red-600">*</span></label>
                <input type="text" name="institution_name" id="institution_name" required maxlength="40" value="{{ $field('institution_name') }}" class="{{ $in }}">
            </div>
            <div>
                <label for="institution_address_line1" class="{{ $label }}">Address line 1</label>
                <input type="text" name="institution_address_line1" id="institution_address_line1" maxlength="60" value="{{ $field('institution_address_line1') }}" class="{{ $in }}">
            </div>
            <div>
                <label for="institution_address_line2" class="{{ $label }}">Address line 2</label>
                <input type="text" name="institution_address_line2" id="institution_address_line2" maxlength="60" value="{{ $field('institution_address_line2') }}" class="{{ $in }}">
            </div>
            <div>
                <label for="contact_phone" class="{{ $label }}">Phone</label>
                <input type="text" name="contact_phone" id="contact_phone" maxlength="60" value="{{ $field('contact_phone') }}" class="{{ $in }}">
            </div>
            <div>
                <label for="contact_email" class="{{ $label }}">Email</label>
                <input type="email" name="contact_email" id="contact_email" maxlength="160" value="{{ $field('contact_email') }}" class="{{ $in }}">
            </div>
            <div>
                <label for="contact_website" class="{{ $label }}">Website</label>
                <input type="text" name="contact_website" id="contact_website" maxlength="80" value="{{ $field('contact_website') }}" class="{{ $in }}">
                <p class="{{ $help }}">Also printed at the foot of the award categories page.</p>
            </div>
        </div>
    </section>

    {{-- ============ Cover wordmark & images ============ --}}
    <section class="rounded-2xl border border-slate-200 bg-white p-6" {!! $forTypes('honors-ball', 'baccalaureate') !!}>
        <h2 class="text-base font-bold text-ink">Cover wordmark &amp; images</h2>
        <p class="{{ $help }}" {!! $forTypes('honors-ball') !!}>The wordmark appears at the bottom right of the cover and on the honors page. Leave all three blank to hide it.</p>
        <p class="{{ $help }}" {!! $forTypes('baccalaureate') !!}>The Baccalaureate program is the agenda page only, which uses the main logo.</p>

        <div class="mt-5 grid gap-5 md:grid-cols-3" {!! $forTypes('honors-ball') !!}>
            <div>
                <label for="mark_top" class="{{ $label }}">Wordmark top line</label>
                <input type="text" name="mark_top" id="mark_top" maxlength="14" value="{{ $field('mark_top') }}" class="{{ $in }}">
            </div>
            <div>
                <label for="mark_script" class="{{ $label }}">Wordmark script word</label>
                <input type="text" name="mark_script" id="mark_script" maxlength="14" value="{{ $field('mark_script') }}" class="{{ $in }}">
            </div>
            <div>
                <label for="mark_bottom" class="{{ $label }}">Wordmark bottom line</label>
                <input type="text" name="mark_bottom" id="mark_bottom" maxlength="14" value="{{ $field('mark_bottom') }}" class="{{ $in }}">
            </div>
        </div>

        <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach (\App\Models\Program::IMAGE_SLOTS as $slot => $meta)
                @php $current = $program->exists ? $program->imageDataUri($slot) : ($slot === 'primary_logo' ? asset('images/starz-logo.png') : null); @endphp
                <div class="flex gap-4 rounded-xl border border-slate-200 p-4" {!! $slot === 'primary_logo' ? $forTypes('honors-ball', 'baccalaureate') : $forTypes('honors-ball') !!}>
                    <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-lg bg-[repeating-conic-gradient(#f1f5f9_0_25%,#fff_0_50%)] bg-[length:12px_12px]">
                        @if ($current)
                            <img src="{{ $current }}" alt="" class="max-h-16 max-w-16 object-contain">
                        @else
                            <span class="text-[10px] text-slate-400">None</span>
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <label for="image-{{ $slot }}" class="text-sm font-semibold text-slate-700">{{ $meta['label'] }}</label>
                        <p class="text-xs text-slate-500">{{ $meta['help'] }}</p>
                        <input type="file" name="images[{{ $slot }}]" id="image-{{ $slot }}" accept="image/png,image/jpeg,image/webp"
                               class="mt-2 block w-full text-xs text-slate-600 file:mr-2 file:rounded-md file:border-0 file:bg-slate-100 file:px-2.5 file:py-1.5 file:text-xs file:font-semibold file:text-slate-700 hover:file:bg-slate-200">
                        @if ($program->exists && $program->asset($slot))
                            <label class="mt-2 flex items-center gap-1.5 text-xs text-slate-600">
                                <input type="checkbox" name="remove_images[]" value="{{ $slot }}" class="rounded accent-red-700">
                                Remove{{ $slot === 'primary_logo' ? ' (revert to the official logo)' : '' }}
                            </label>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
        <p class="{{ $help }} mt-3">PNG, JPEG or WebP, up to 5 MB. Large images are scaled down automatically; transparent PNGs work best on the black pages.</p>
    </section>

    {{-- ============ Agenda ============ --}}
    <section class="rounded-2xl border border-slate-200 bg-white p-6" data-repeater="agenda" {!! $forTypes('honors-ball', 'baccalaureate') !!}>
        <h2 class="text-base font-bold text-ink">Program agenda</h2>
        <p class="{{ $help }}">Printed in order on page 2. “Highlight” prints large and bold (e.g. Presentation of Awards); “Section heading” prints bold, for a group label. Up to 26 items of up to 80 characters.</p>

        <ol class="mt-5 space-y-2" data-repeater-list>
            @foreach ($agendaRows as $i => $row)
                @include('admin.programs._agenda-row', ['i' => $i, 'row' => $row])
            @endforeach
        </ol>
        <template data-repeater-template>
            @include('admin.programs._agenda-row', ['i' => '__INDEX__', 'row' => ['style' => 'item', 'title' => '']])
        </template>

        <button type="button" data-repeater-add class="mt-4 inline-flex items-center rounded-lg border border-dashed border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:border-slate-400 hover:bg-slate-50">
            + Add agenda item
        </button>
    </section>

    {{-- ============ Honors page ============ --}}
    <section class="rounded-2xl border border-slate-200 bg-white p-6" {!! $forTypes('honors-ball') !!}>
        <h2 class="text-base font-bold text-ink">Honors page</h2>
        <p class="{{ $help }}">The black page with large stacked lettering (page 3). Leave a line blank to omit it.</p>

        <div class="mt-5 grid gap-5 md:grid-cols-4">
            <div>
                <label for="honors_line1" class="{{ $label }}">Line 1 (white)</label>
                <input type="text" name="honors_line1" id="honors_line1" maxlength="24" value="{{ $field('honors_line1') }}" placeholder="e.g. Ninth" class="{{ $in }}">
            </div>
            <div>
                <label for="honors_ribbon" class="{{ $label }}">Ribbon</label>
                <input type="text" name="honors_ribbon" id="honors_ribbon" maxlength="30" value="{{ $field('honors_ribbon') }}" class="{{ $in }}">
            </div>
            <div>
                <label for="honors_line2" class="{{ $label }}">Line 2 (white)</label>
                <input type="text" name="honors_line2" id="honors_line2" maxlength="24" value="{{ $field('honors_line2') }}" class="{{ $in }}">
            </div>
            <div>
                <label for="honors_line3" class="{{ $label }}">Line 3 (orange)</label>
                <input type="text" name="honors_line3" id="honors_line3" maxlength="24" value="{{ $field('honors_line3') }}" class="{{ $in }}">
            </div>
        </div>
    </section>

    {{-- ============ Award categories ============ --}}
    <section class="rounded-2xl border border-slate-200 bg-white p-6" data-repeater="awards" {!! $forTypes('honors-ball') !!}>
        <h2 class="text-base font-bold text-ink">Award categories</h2>
        <p class="{{ $help }}">Printed in three columns on page 4, in this order. Up to 12 categories; descriptions can total up to {{ number_format(\App\Http\Requests\ProgramRequest::MAX_AWARD_TEXT) }} characters (text shrinks automatically to fit).</p>

        <div class="mt-5 max-w-md">
            <label for="awards_heading" class="{{ $label }}">Page heading</label>
            <input type="text" name="awards_heading" id="awards_heading" maxlength="80" value="{{ $field('awards_heading') }}" class="{{ $in }}">
        </div>

        <ol class="mt-5 space-y-3" data-repeater-list>
            @foreach ($awardRows as $i => $row)
                @include('admin.programs._award-row', ['i' => $i, 'row' => $row])
            @endforeach
        </ol>
        <template data-repeater-template>
            @include('admin.programs._award-row', ['i' => '__INDEX__', 'row' => ['name' => '', 'description' => '']])
        </template>

        <button type="button" data-repeater-add class="mt-4 inline-flex items-center rounded-lg border border-dashed border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:border-slate-400 hover:bg-slate-50">
            + Add award category
        </button>
    </section>

    {{-- ============ Convocation program PDF ============ --}}
    <section class="rounded-2xl border border-slate-200 bg-white p-6" {!! $forTypes('convocation') !!}>
        <h2 class="text-base font-bold text-ink">Convocation program PDF</h2>
        <p class="{{ $help }}">
            Upload the complete, already-designed program. Nothing is generated from this form for Convocation —
            this file is what Download PDF and the Convocation page serve. PDF only, up to {{ number_format($pdfMaxKb / 1024, 1) }} MB.
        </p>

        @if ($program->exists && $program->type === 'convocation' && $program->hasPdf())
            <p class="mt-4 rounded-lg bg-slate-50 px-4 py-2.5 text-sm text-slate-700">
                Current file: uploaded {{ $program->pdf_generated_at->format('M j, Y g:i A') }} &middot; {{ number_format($program->pdf_size / 1024) }} KB &middot;
                <a href="{{ route('admin.programs.download', $program) }}" class="font-semibold text-ink underline">Download</a>.
                Choosing a new file replaces it.
            </p>
        @endif

        <input type="file" name="program_pdf" id="program_pdf" accept="application/pdf,.pdf"
               class="mt-4 block w-full text-sm text-slate-600 file:mr-3 file:rounded-md file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-slate-700 hover:file:bg-slate-200">
        @error('program_pdf') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </section>

    {{-- ============ Save ============ --}}
    <div class="sticky bottom-0 -mx-6 flex flex-wrap items-center gap-3 border-t border-slate-200 bg-slate-50/95 px-6 py-4 backdrop-blur">
        <button type="submit" name="action" value="save"
                class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-800 transition hover:bg-slate-50">
            Save
        </button>
        <button type="submit" name="action" value="generate" {!! $forTypes('honors-ball', 'baccalaureate') !!}
                class="inline-flex items-center rounded-lg bg-ink px-5 py-2.5 text-sm font-semibold text-gold-300 transition hover:bg-neutral-800">
            Save &amp; generate PDF
        </button>
        <a href="{{ route('admin.programs.index') }}" class="text-sm font-medium text-slate-500 hover:text-slate-700">Cancel</a>
    </div>
</div>
