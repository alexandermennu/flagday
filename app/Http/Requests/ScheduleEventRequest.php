<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ScheduleEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:120'],
            'event_date' => ['nullable', 'date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'time_note' => ['nullable', 'string', 'max:60'],
            'venue' => ['nullable', 'string', 'max:160'],
            'theme' => ['nullable', 'string', 'max:160'],
            'speaker_label' => ['nullable', 'string', 'max:40'],
            'speaker' => ['nullable', 'string', 'max:160'],
            'guest_of_honor' => ['nullable', 'string', 'max:160'],
            'attendance' => ['nullable', 'string', 'max:160'],
            'dress_code_label' => ['nullable', 'string', 'max:40'],
            'dress_code' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
            'notes_text' => ['nullable', 'string', 'max:5000'],
            'is_published' => ['boolean'],
        ];
    }

    public function attributes(): array
    {
        return ['notes_text' => 'information lines', 'end_time' => 'end time', 'start_time' => 'start time'];
    }

    /**
     * Validated fields, with the "one line per note" textarea split into the notes array.
     *
     * @return array<string, mixed>
     */
    public function eventData(): array
    {
        $data = collect($this->validated())->except('notes_text')->all();
        $data['notes'] = collect(preg_split('/\R/', (string) $this->input('notes_text')))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();
        $data['is_published'] = $this->boolean('is_published');

        return $data;
    }
}
