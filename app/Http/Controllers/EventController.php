<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use App\Models\Program;
use App\Models\ProgramPdf;
use App\Support\Graduation;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

/**
 * Public detail page for each graduation event (Honors Ball, Baccalaureate,
 * Convocation). Content comes from config/graduation.php.
 */
class EventController extends Controller
{
    public function show(string $slug): View
    {
        $event = Graduation::find($slug);

        abort_if($event === null, 404);

        return view('events.show', [
            'event' => $event,
            'otherEvents' => Graduation::events()->except($slug),
            'gallery' => $slug === 'honors-ball' ? $this->honorsBallGallery() : null,
        ]);
    }

    /**
     * The Honors Ball gallery with its published photos, or null when it is hidden or has
     * none published — the section is then left off the page.
     */
    private function honorsBallGallery(): ?Gallery
    {
        try {
            $gallery = Gallery::where('key', Gallery::HONORS_BALL)->where('is_visible', true)->first();
        } catch (QueryException) {
            return null; // gallery tables not migrated yet
        }

        if ($gallery === null) {
            return null;
        }

        $gallery->setRelation('photos', $gallery->publishedPhotos());

        return $gallery->photos->isEmpty() ? null : $gallery;
    }

    /**
     * The flipbook viewer for the event's published program. The PDF itself is rendered
     * in the browser (resources/js/flipbook.js) from the program() route below.
     */
    public function viewer(string $slug): View
    {
        $event = Graduation::find($slug);

        abort_if($event === null || $event['flipbook_url'] === null, 404);

        return view('events.program-viewer', ['event' => $event]);
    }

    /** Serves the event's published program PDF — the copy frozen at its last publish. */
    public function program(Request $request, string $slug): Response
    {
        $program = Program::publishedFor($slug);
        abort_if($program === null, 404);

        $bytes = base64_decode((string) ProgramPdf::where('program_id', $program->id)->value('published_data'));
        $disposition = $request->boolean('download') ? 'attachment' : 'inline';

        return response($bytes, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => $disposition.'; filename="'.$program->pdfFilename().'"',
            // Laravel Cloud's edge (Cloudflare) caches responses without Cache-Control; a
            // republished program must show up straight away.
            'Cache-Control' => 'no-cache, must-revalidate',
        ]);
    }
}
