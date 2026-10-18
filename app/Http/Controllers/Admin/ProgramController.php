<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\ProgramDoesNotFit;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProgramRequest;
use App\Models\Program;
use App\Services\ProgramImageProcessor;
use App\Services\ProgramPdfRenderer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Admin management of printable graduation programs: content forms, PDF generation
 * (stored, so Download always serves the last explicitly generated version), on-the-fly
 * preview, and publishing.
 */
class ProgramController extends Controller
{
    public function __construct(
        private ProgramPdfRenderer $renderer,
        private ProgramImageProcessor $images,
    ) {
    }

    public function index(): View
    {
        return view('admin.programs.index', [
            'programs' => Program::query()->orderByDesc('updated_at')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.programs.create', [
            'program' => new Program(Program::templateDefaults()),
        ]);
    }

    public function store(ProgramRequest $request): RedirectResponse
    {
        $program = DB::transaction(function () use ($request) {
            $program = Program::create($request->programData());
            $this->syncImages($program, $request);

            return $program;
        });

        return $this->afterSave($program, $request, 'Program created.');
    }

    public function edit(Program $program): View
    {
        return view('admin.programs.edit', [
            'program' => $program->load('assets'),
        ]);
    }

    public function update(ProgramRequest $request, Program $program): RedirectResponse
    {
        DB::transaction(function () use ($program, $request) {
            $program->fill($request->programData());
            // Always bump updated_at so an image-only change still marks the PDF out of date.
            $program->updated_at = now();
            $program->save();
            $this->syncImages($program, $request);
        });

        return $this->afterSave($program, $request, 'Program saved.');
    }

    public function destroy(Program $program): RedirectResponse
    {
        $program->delete();

        return redirect()->route('admin.programs.index')->with('success', "Deleted “{$program->title}”.");
    }

    /** Explicitly (re)generates and stores the PDF from the saved content. */
    public function generate(Program $program): RedirectResponse
    {
        if ($error = $this->storePdf($program)) {
            return back()->withErrors(['pdf' => $error]);
        }

        return back()->with('success', 'PDF generated. Use Download PDF to save it.');
    }

    /** Marks the program published as a new version and regenerates its PDF. */
    public function publish(Program $program): RedirectResponse
    {
        if ($error = $this->storePdf($program)) {
            return back()->withErrors(['pdf' => "Not published: {$error}"]);
        }

        Program::withoutTimestamps(function () use ($program) {
            $program->forceFill([
                'published_at' => now(),
                'published_version' => $program->published_version + 1,
            ])->save();
        });

        return back()->with('success', "Published version {$program->published_version}. The PDF was regenerated.");
    }

    public function download(Program $program): Response|RedirectResponse
    {
        $pdf = $program->pdf;

        if (! $pdf) {
            return back()->withErrors(['pdf' => 'No PDF has been generated yet. Click “Generate PDF” first.']);
        }

        return response($pdf->bytes(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$program->pdfFilename().'"',
        ]);
    }

    /** Renders the current saved content without storing it, shown inline in the browser. */
    public function preview(Program $program): Response|RedirectResponse
    {
        try {
            $bytes = $this->renderer->render($program);
        } catch (ProgramDoesNotFit $e) {
            return back()->withErrors(['pdf' => $e->getMessage()]);
        }

        return response($bytes, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="preview-'.$program->pdfFilename().'"',
        ]);
    }

    private function afterSave(Program $program, ProgramRequest $request, string $message): RedirectResponse
    {
        $redirect = redirect()->route('admin.programs.edit', $program);

        if ($request->input('action') === 'generate') {
            return ($error = $this->storePdf($program))
                ? $redirect->with('success', $message)->withErrors(['pdf' => $error])
                : $redirect->with('success', "{$message} PDF generated.");
        }

        return $redirect->with('success', $message);
    }

    /** Renders and stores the PDF. Returns why it couldn't be generated, or null on success. */
    private function storePdf(Program $program): ?string
    {
        $program->load('assets');

        try {
            $bytes = $this->renderer->render($program);
        } catch (ProgramDoesNotFit $e) {
            return $e->getMessage();
        }

        DB::transaction(function () use ($program, $bytes) {
            $program->pdf()->updateOrCreate([], ['data' => base64_encode($bytes)]);

            // Metadata only — leaving updated_at alone keeps the "out of date" check meaningful.
            Program::withoutTimestamps(function () use ($program, $bytes) {
                $program->forceFill([
                    'pdf_generated_at' => now(),
                    'pdf_size' => strlen($bytes),
                    'pdf_page_count' => ProgramPdfRenderer::PAGE_COUNT,
                ])->save();
            });
        });

        return null;
    }

    private function syncImages(Program $program, ProgramRequest $request): void
    {
        foreach ((array) $request->input('remove_images', []) as $slot) {
            $program->assets()->where('slot', $slot)->delete();
        }

        foreach (array_keys(Program::IMAGE_SLOTS) as $slot) {
            if ($file = $request->file("images.$slot")) {
                $program->assets()->updateOrCreate(['slot' => $slot], $this->images->process($file));
            }
        }

        $program->unsetRelation('assets');
    }
}
