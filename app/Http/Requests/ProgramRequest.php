<?php

namespace App\Http\Requests;

use App\Models\Program;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProgramRequest extends FormRequest
{
    /** Combined award-description length that still fits one page at the smallest text tier. */
    public const MAX_AWARD_TEXT = 6000;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Rows the admin added and left completely blank are dropped rather than failing validation.
        $this->merge([
            'agenda' => array_values(array_filter(
                (array) $this->input('agenda', []),
                fn ($row) => filled($row['title'] ?? null),
            )),
            'awards' => array_values(array_filter(
                (array) $this->input('awards', []),
                fn ($row) => filled($row['name'] ?? null) || filled($row['description'] ?? null),
            )),
        ]);
    }

    /**
     * Form sections each type does not use. Their (hidden) fields are excluded from
     * validation and from the saved data, so a type switch never blocks a save on a field
     * the admin can't see, and never wipes content saved for another type.
     */
    private const UNUSED_BY_TYPE = [
        'honors-ball' => [],
        'baccalaureate' => ['mark_top', 'mark_script', 'mark_bottom', 'honors_line1', 'honors_ribbon', 'honors_line2', 'honors_line3', 'awards_heading', 'awards'],
        'convocation' => ['mark_top', 'mark_script', 'mark_bottom', 'honors_line1', 'honors_ribbon', 'honors_line2', 'honors_line3', 'awards_heading', 'awards', 'agenda', 'images'],
    ];

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $image = ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:5120'];

        $rules = [
            'type' => ['required', Rule::in(array_keys(Program::TYPES))],
            'program_pdf' => ['exclude_unless:type,convocation', 'bail', 'nullable', 'file', 'mimes:pdf', 'mimetypes:application/pdf', 'max:'.Program::pdfUploadMaxKb(), function (string $attribute, mixed $value, Closure $fail) {
                $handle = fopen($value->getRealPath(), 'rb');
                $signature = $handle ? fread($handle, 5) : '';
                $handle && fclose($handle);
                if ($signature !== '%PDF-') {
                    $fail('The program PDF must be a valid PDF file.');
                }
            }],
            'title' => ['required', 'string', 'max:80'],
            'class_year' => ['required', 'string', 'max:9'],
            'edition_label' => ['nullable', 'string', 'max:60'],
            'event_date' => ['nullable', 'date'],
            'event_time' => ['nullable', 'date_format:H:i'],
            'venue_name' => ['nullable', 'string', 'max:80'],
            'venue_address' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:900'],
            'hosted_by' => ['nullable', 'string', 'max:80'],

            'institution_name' => ['required', 'string', 'max:40'],
            'institution_address_line1' => ['nullable', 'string', 'max:60'],
            'institution_address_line2' => ['nullable', 'string', 'max:60'],
            'contact_phone' => ['nullable', 'string', 'max:60'],
            'contact_email' => ['nullable', 'email', 'max:160'],
            'contact_website' => ['nullable', 'string', 'max:80'],

            'mark_top' => ['nullable', 'string', 'max:14'],
            'mark_script' => ['nullable', 'string', 'max:14'],
            'mark_bottom' => ['nullable', 'string', 'max:14'],

            'honors_line1' => ['nullable', 'string', 'max:24'],
            'honors_ribbon' => ['nullable', 'string', 'max:30'],
            'honors_line2' => ['nullable', 'string', 'max:24'],
            'honors_line3' => ['nullable', 'string', 'max:24'],

            'agenda' => ['array', 'max:26'],
            'agenda.*.title' => ['required', 'string', 'max:80'],
            'agenda.*.style' => ['required', Rule::in(array_keys(Program::AGENDA_STYLES))],

            'awards_heading' => ['nullable', 'string', 'max:80'],
            'awards' => ['array', 'max:12', function (string $attribute, mixed $value, Closure $fail) {
                $total = collect($value)->sum(fn ($award) => mb_strlen($award['description'] ?? ''));
                if ($total > self::MAX_AWARD_TEXT) {
                    $fail('The award descriptions total '.number_format($total).' characters; they must total no more than '.number_format(self::MAX_AWARD_TEXT).' to fit on one page.');
                }
            }],
            'awards.*.name' => ['required', 'string', 'max:80'],
            'awards.*.description' => ['nullable', 'string', 'max:1500'],

            'images' => ['array'],
            ...collect(Program::IMAGE_SLOTS)->keys()->mapWithKeys(fn ($slot) => ["images.$slot" => $image])->all(),
            'remove_images' => ['array'],
            'remove_images.*' => [Rule::in(array_keys(Program::IMAGE_SLOTS))],
        ];

        // Prefix rules for fields this type doesn't use with "exclude" (whole sections,
        // including their nested agenda.* / awards.* / images.* rules).
        $unused = self::UNUSED_BY_TYPE[$this->input('type')] ?? [];
        foreach ($rules as $field => $fieldRules) {
            $root = strtok($field, '.');
            if (in_array($root, $unused, true) || ($root === 'remove_images' && in_array('images', $unused, true))) {
                $rules[$field] = ['exclude'];
            }
        }

        return $rules;
    }

    public function attributes(): array
    {
        return [
            'agenda.*.title' => 'agenda item',
            'awards.*.name' => 'award name',
            'awards.*.description' => 'award description',
            'images.*' => 'image',
            'program_pdf' => 'program PDF',
            'honors_line1' => 'honors page line 1',
            'honors_ribbon' => 'honors ribbon text',
            'honors_line2' => 'honors page line 2',
            'honors_line3' => 'honors page line 3',
        ];
    }

    /**
     * The program's own columns from the validated input (images are handled separately).
     *
     * @return array<string, mixed>
     */
    public function programData(): array
    {
        $data = collect($this->validated())->except(['images', 'remove_images', 'program_pdf'])->all();

        // Sections excluded for this type are left untouched (not emptied) when saving.
        if (array_key_exists('agenda', $data)) {
            $data['agenda'] = array_map(fn ($row) => ['style' => $row['style'], 'title' => $row['title']], $data['agenda'] ?? []);
        }
        if (array_key_exists('awards', $data)) {
            $data['awards'] = array_map(fn ($row) => ['name' => $row['name'], 'description' => $row['description'] ?? ''], $data['awards'] ?? []);
        }

        return $data;
    }
}
