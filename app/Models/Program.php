<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

/**
 * A printable four-page graduation program (cover, agenda, honors page, award
 * categories) whose content is entered in the admin and rendered to PDF by
 * App\Services\ProgramPdfRenderer.
 */
class Program extends Model
{
    use HasFactory;

    public const AGENDA_STYLES = [
        'item' => 'Agenda item',
        'feature' => 'Highlight (large, bold)',
        'section' => 'Section heading (bold)',
    ];

    /**
     * Image slots the admin can upload. primary_logo falls back to the official Starz
     * logo; the rest are simply omitted from the layout when empty.
     */
    public const IMAGE_SLOTS = [
        'primary_logo' => ['label' => 'Main logo', 'help' => 'Cover (bottom left) and agenda page. Defaults to the official Starz University logo.'],
        'seal_left' => ['label' => 'Left seal', 'help' => 'Left of the university name on the cover, and on the honors page.'],
        'seal_right' => ['label' => 'Right seal', 'help' => 'Right of the university name on the cover, and on the honors page.'],
        'corner_badge' => ['label' => 'Corner badge', 'help' => 'Top-right tab on the cover, e.g. a class-year graphic.'],
        'cover_accent' => ['label' => 'Cover accent', 'help' => 'Decorative image on the right edge of the cover, e.g. a graduation cap (transparent PNG works best).'],
    ];

    protected $guarded = ['id', 'published_at', 'published_version', 'pdf_generated_at', 'pdf_size', 'pdf_page_count'];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'agenda' => 'array',
            'awards' => 'array',
            'published_at' => 'datetime',
            'pdf_generated_at' => 'datetime',
        ];
    }

    public function assets(): HasMany
    {
        return $this->hasMany(ProgramAsset::class);
    }

    public function pdf(): HasOne
    {
        return $this->hasOne(ProgramPdf::class);
    }

    public function asset(string $slot): ?ProgramAsset
    {
        return $this->assets->firstWhere('slot', $slot);
    }

    /**
     * The image for a slot as a data URI (what dompdf embeds), with the official logo
     * as the fallback for the main logo slot.
     */
    public function imageDataUri(string $slot): ?string
    {
        if ($asset = $this->asset($slot)) {
            return $asset->dataUri();
        }

        if ($slot === 'primary_logo') {
            return 'data:image/png;base64,'.base64_encode(file_get_contents(public_path('images/starz-logo.png')));
        }

        return null;
    }

    public function hasPdf(): bool
    {
        return $this->pdf_generated_at !== null;
    }

    /** True when content changed after the stored PDF was generated. */
    public function pdfIsStale(): bool
    {
        return $this->hasPdf() && $this->updated_at->greaterThan($this->pdf_generated_at);
    }

    public function pdfFilename(): string
    {
        return Str::slug("{$this->institution_name} {$this->title} {$this->class_year}").'-program.pdf';
    }

    /**
     * Starting content for a new program, taken from the Special Honors Awards program
     * the university supplied. Event-specific details (date, time, venue, host, edition
     * number) are deliberately left blank rather than carried over from a past year.
     *
     * @return array<string, mixed>
     */
    public static function templateDefaults(): array
    {
        return [
            'title' => 'Special Honors Awards',
            'class_year' => (string) config('graduation.class_year'),
            'edition_label' => 'Annual Special Honors Awards',
            'institution_name' => config('graduation.institution'),
            'institution_address_line1' => 'Airfield, Sinkor',
            'institution_address_line2' => 'Montserrado Co. Liberia',
            'contact_website' => 'www.starzit.com',
            'mark_top' => 'Special',
            'mark_script' => 'Honors',
            'mark_bottom' => 'Awards',
            'honors_ribbon' => 'Annual Special',
            'honors_line2' => 'Honors',
            'honors_line3' => 'Awards',
            'awards_heading' => 'Awards Categories & Description',
            'agenda' => [
                ['style' => 'item', 'title' => 'Invocation'],
                ['style' => 'item', 'title' => 'Welcome Remarks'],
                ['style' => 'item', 'title' => 'Significance of Starz Award Ceremony'],
                ['style' => 'item', 'title' => 'Interlude'],
                ['style' => 'item', 'title' => 'Induction of the Honors Society'],
                ['style' => 'item', 'title' => 'Interlude'],
                ['style' => 'feature', 'title' => 'Presentation of Awards'],
                ['style' => 'section', 'title' => 'Honoring & Recognition Ceremony:'],
                ['style' => 'item', 'title' => 'Starz Best Administrator Awards'],
                ['style' => 'item', 'title' => 'Starz Best Teacher Awards'],
                ['style' => 'item', 'title' => 'Starz Best Customer Service Awards'],
                ['style' => 'item', 'title' => 'Starz Humanitarian Awards'],
                ['style' => 'item', 'title' => 'Starz Honor Society Awards'],
                ['style' => 'item', 'title' => 'Latim Dathong Academic and Excellence Award'],
                ['style' => 'item', 'title' => 'Interlude'],
                ['style' => 'item', 'title' => 'Remarks'],
                ['style' => 'item', 'title' => 'Vote of thanks'],
                ['style' => 'item', 'title' => 'Musical Interlude'],
            ],
            'awards' => [
                ['name' => 'Starz Leadership Award', 'description' => "Starz Leadership award This award is an honor given to a student who has unselfishly given his/her time and energy in serving the institution above and beyond normal expectations. has demonstrated exceptional leadership through their countless involvement with student affairs, resulting to recognizable impact. has the courage and vision to actively advocate for positive change on campus and/or in the world. has demonstrated passion, spirit and a committed desire to make Starz University a better place through promotions of the University's norms and standards."],
                ['name' => 'Starz Customer Service Awards', 'description' => 'The Starz Customer Service Award honors customer care agents who go above and beyond to ensure the satisfaction and trust of Starz students and customers. Their dedication, professionalism, and commitment to service set a strong example of excellence and kindness. These agents not only enhance the customer experience but also contribute to the overall success of StarzNation.'],
                ['name' => 'Starz Honor Society Award', 'description' => "This award is an honor given to a student who has maintained an his/her membership status of the Starz Honors Program over the course of study has demonstrated exemplary talents and academic excellence but beyond all, a student who has shown the edge for innovation. Notably, admission into the Starz Honor Society's program is by invitation-only. Students are nominated based on academic excellence by the Department of Research & Development and approval by the President of the University."],
                ['name' => 'Starz Best Administrator and Teacher Award', 'description' => 'In addition to professional classroom standards, Starz University attaches significant value to innovation and dedication in the role of teaching. The Starz Best Teacher award is presented to an exceptional teacher who has demonstrated an extraordinary level of service, dedication, and commitment above and beyond the classroom call of duty. The recipient of this award has proven an unusual level of effectiveness, leadership, and inspirational teaching, thus impacting students, the institution, and their community.'],
                ['name' => 'Starz Humanitarian Award', 'description' => 'The Starz Humanitarian Awards honor remarkable individuals who have dedicated their lives to creating a better world. Through acts of kindness, selflessness, and tireless efforts, these heroes remind us that even small gestures can have a monumental impact. Join us in celebrating their compassion, their commitment, and their ability to inspire positive change for the global community.'],
                ['name' => 'Latim Dathong Academic & Excellence Award', 'description' => 'This award is an honor given to a student who has achieved a grade point average of 3.5 or above has been recommended by faculty as an outstanding student both academically and behavioral or conduct.'],
                ['name' => 'Starz Legend Award', 'description' => "The Starz Legend Award represents the highest recognition bestowed by Starz University. It honors iconic leaders whose influence reaches far beyond personal success, shaping communities, nations, and global spaces. Reserved for individuals who embody vision, courage, and an enduring legacy of impact, this award reflects Starz University's commitment to celebrating those who redefine excellence and inspire change on the world stage. By recognizing these trailblazers, we affirm our belief that true leadership is measured by the lives transformed and the futures shaped through extraordinary service and achievement."],
            ],
        ];
    }
}
