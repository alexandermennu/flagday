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
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $image = ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:5120'];

        return [
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
    }

    public function attributes(): array
    {
        return [
            'agenda.*.title' => 'agenda item',
            'awards.*.name' => 'award name',
            'awards.*.description' => 'award description',
            'images.*' => 'image',
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
        $data = collect($this->validated())->except(['images', 'remove_images'])->all();
        $data['agenda'] = array_map(fn ($row) => ['style' => $row['style'], 'title' => $row['title']], $data['agenda'] ?? []);
        $data['awards'] = array_map(fn ($row) => ['name' => $row['name'], 'description' => $row['description'] ?? ''], $data['awards'] ?? []);

        return $data;
    }
}
