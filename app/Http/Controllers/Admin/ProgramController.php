<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\ProgramDoesNotFit;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProgramRequest;
use App\Models\Program;
use App\Services\ProgramImageProcessor;
use App\Services\ProgramPdfRenderer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Admin management of graduation programs: content forms, PDF generation (stored, so
 * Download always serves the last explicitly generated version), on-the-fly preview, and
 * publishing to the program type's public event page. Honors Ball and Baccalaureate PDFs
 * are generated from the form; Convocation programs are an uploaded PDF.
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

    public function create(Request $request): View
    {
        $type = array_key_exists($request->query('type'), Program::TYPES) ? $request->query('type') : 'honors-ball';

        return view('admin.programs.create', [
            'program' => new Program(Program::templateDefaults($type)),
        ]);
    }

    public function store(ProgramRequest $request): RedirectResponse
    {
        $program = DB::transaction(function () use ($request) {
            $program = Program::create($request->programData());
            $this->syncImages($program, $request);
            $this->storeUpload($program, $request->file('program_pdf'));

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
        $typeChanged = $request->input('type') !== $program->type;

        DB::transaction(function () use ($program, $request, $typeChanged) {
            $program->fill($request->programData());
            // Always bump updated_at so an image-only change still marks the PDF out of date.
            $program->updated_at = now();
            $program->save();

            // A PDF made for the old type must never be served (or stay published) under the new one.
            if ($typeChanged) {
                $program->pdf()->delete();
                $program->forceFill(['published_at' => null, 'pdf_generated_at' => null, 'pdf_size' => null, 'pdf_page_count' => null])->save();
            }

            $this->syncImages($program, $request);
            $this->storeUpload($program, $request->file('program_pdf'));
        });

        $message = $typeChanged
            ? "Program saved as {$program->typeLabel()}. Its previous PDF was removed and it is unpublished until you publish again."
            : 'Program saved.';

        return $this->afterSave($program, $request, $message);
    }

    public function destroy(Program $program): RedirectResponse
    {
        $program->delete();

        return redirect()->route('admin.programs.index')->with('success', "Deleted “{$program->title}”.");
    }

    /** Explicitly (re)generates and stores the PDF from the saved content. */
    public function generate(Program $program): RedirectResponse
    {
        if (! $program->isGenerated()) {
            return back()->withErrors(['pdf' => "{$program->typeLabel()} programs use an uploaded PDF and are not generated."]);
        }

        if ($error = $this->storePdf($program)) {
            return back()->withErrors(['pdf' => $error]);
        }

        return back()->with('success', 'PDF generated. Use Download PDF to save it.');
    }

    /**
     * Marks the program published as a new version and freezes a copy of its PDF for the
     * public event page — regenerating it first for generated types.
     */
    public function publish(Program $program): RedirectResponse
    {
        if ($program->isGenerated()) {
            if ($error = $this->storePdf($program)) {
                return back()->withErrors(['pdf' => "Not published: {$error}"]);
            }
        } elseif (! $program->hasPdf()) {
            return back()->withErrors(['pdf' => 'Not published: upload the program PDF first.']);
        }

        DB::transaction(function () use ($program) {
            $pdf = $program->pdf()->firstOrFail();
            $pdf->update(['published_data' => $pdf->data]);

            Program::withoutTimestamps(function () use ($program) {
                $program->forceFill([
                    'published_at' => now(),
                    'published_version' => $program->published_version + 1,
                ])->save();
            });
        });

        $page = Program::TYPES[$program->type];

        return back()->with('success', $program->isGenerated()
            ? "Published version {$program->published_version} to the {$page} page. The PDF was regenerated."
            : "Published version {$program->published_version} to the {$page} page.");
    }

    public function download(Program $program): Response|RedirectResponse
    {
        $pdf = $program->pdf;

        if (! $pdf) {
            return back()->withErrors(['pdf' => $program->isGenerated()
                ? 'No PDF has been generated yet. Click “Generate PDF” first.'
                : 'No PDF has been uploaded yet.']);
        }

        return response($pdf->bytes(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$program->pdfFilename().'"',
        ]);
    }

    /** Renders the current saved content without storing it, shown inline in the browser. */
    public function preview(Program $program): Response|RedirectResponse
    {
        if (! $program->isGenerated()) {
            return back()->withErrors(['pdf' => "{$program->typeLabel()} programs use an uploaded PDF and have no generated preview."]);
        }

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

        if ($request->input('action') === 'generate' && $program->isGenerated()) {
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
                    'pdf_page_count' => ProgramPdfRenderer::pageCount($program->type),
                ])->save();
            });
        });

        return null;
    }

    /** Stores an uploaded Convocation PDF as the program's working PDF (replacing any previous upload). */
    private function storeUpload(Program $program, ?UploadedFile $file): void
    {
        if (! $file || $program->isGenerated()) {
            return;
        }

        $bytes = (string) file_get_contents($file->getRealPath());
        $program->pdf()->updateOrCreate([], ['data' => base64_encode($bytes)]);

        Program::withoutTimestamps(function () use ($program, $bytes) {
            $program->forceFill([
                'pdf_generated_at' => now(),
                'pdf_size' => strlen($bytes),
                'pdf_page_count' => null,
            ])->save();
        });
    }

    private function syncImages(Program $program, ProgramRequest $request): void
    {
        // Convocation has no generated pages, so its (hidden) image fields are ignored.
        if (! $program->isGenerated()) {
            return;
        }

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
