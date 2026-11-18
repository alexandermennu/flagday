<?php

namespace Tests\Feature\Admin;

use App\Models\Program;
use App\Models\ProgramPdfPart;
use App\Models\ProgramPdfUpload;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class ProgramPdfUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    private function start(int $size): TestResponse
    {
        return $this->postJson(route('admin.program-uploads.store'), ['size' => $size]);
    }

    private function sendChunk(string $token, int $offset, string $bytes): TestResponse
    {
        return $this->call('POST', route('admin.program-uploads.append', $token).'?offset='.$offset, [], [], [], [
            'CONTENT_TYPE' => 'application/octet-stream',
            'HTTP_ACCEPT' => 'application/json',
        ], $bytes);
    }

    /** Uploads the bytes the way the admin form does, returning the finished upload's token. */
    private function upload(string $bytes): string
    {
        $start = $this->start(strlen($bytes))->assertCreated();
        $chunkSize = $start->json('chunk_size');

        for ($offset = 0; $offset < strlen($bytes); $offset += $chunkSize) {
            $this->sendChunk($start->json('token'), $offset, substr($bytes, $offset, $chunkSize))->assertOk();
        }

        return $start->json('token');
    }

    /** A PDF-signed file several chunks (and several streaming pieces) long, with uneven bytes throughout. */
    private function largePdf(): string
    {
        return '%PDF-1.7'.random_bytes(5 * 1024 * 1024 + 7).'%%EOF';
    }

    public function test_a_chunked_upload_is_saved_published_and_served_byte_for_byte(): void
    {
        $bytes = $this->largePdf();
        $token = $this->upload($bytes);

        $this->post(route('admin.programs.store'), array_merge(Program::factory()->raw(), [
            'type' => 'convocation',
            'program_pdf_upload' => $token,
        ]))->assertSessionHasNoErrors();

        $program = Program::sole();
        $this->assertSame(strlen($bytes), $program->pdf_size);
        $this->assertDatabaseCount('program_pdf_uploads', 0);

        $download = $this->get(route('admin.programs.download', $program))->assertOk();
        $download->assertHeader('Content-Length', (string) strlen($bytes));
        $this->assertTrue($download->streamedContent() === $bytes, 'The download matches the uploaded file.');

        $this->post(route('admin.programs.publish', $program))->assertSessionHasNoErrors();

        $view = $this->get(route('events.program', 'convocation'))->assertOk();
        $view->assertHeader('Content-Length', (string) strlen($bytes));
        $this->assertTrue($view->streamedContent() === $bytes, 'The published copy matches the uploaded file.');
    }

    public function test_no_stored_value_grows_with_the_size_of_the_file(): void
    {
        // MySQL refuses any single value over max_allowed_packet (32 MB on production), which
        // is what stopped a 30.7 MB upload at 82% while it was stored as one growing value.
        $program = Program::factory()->create(['type' => 'convocation']);
        $this->put(route('admin.programs.update', $program), array_merge(Program::factory()->raw(), [
            'type' => 'convocation',
            'program_pdf_upload' => $this->upload($this->largePdf()),
        ]))->assertSessionHasNoErrors();
        $this->post(route('admin.programs.publish', $program))->assertSessionHasNoErrors();

        $this->assertGreaterThan(2, ProgramPdfPart::where('variant', 'published')->count());
        $this->assertLessThanOrEqual(6 * 1024 * 1024, (int) ProgramPdfPart::query()->selectRaw('MAX(LENGTH(data)) AS longest')->value('longest'));
    }

    public function test_a_chunked_upload_replaces_an_existing_pdf(): void
    {
        $program = Program::factory()->create(['type' => 'convocation']);
        $this->put(route('admin.programs.update', $program), array_merge(Program::factory()->raw(), [
            'type' => 'convocation',
            'program_pdf_upload' => $this->upload('%PDF-1.4 first'),
        ]))->assertSessionHasNoErrors();

        $this->put(route('admin.programs.update', $program), array_merge(Program::factory()->raw(), [
            'type' => 'convocation',
            'program_pdf_upload' => $this->upload('%PDF-1.4 second'),
        ]))->assertSessionHasNoErrors();

        $this->assertSame('%PDF-1.4 second', $this->get(route('admin.programs.download', $program))->streamedContent());
        $this->assertDatabaseCount('program_pdfs', 1);
    }

    public function test_files_up_to_100_mb_are_accepted(): void
    {
        $this->start(100 * 1024 * 1024)->assertCreated();
        $this->start(100 * 1024 * 1024 + 1)->assertUnprocessable()->assertJsonValidationErrors('size');
    }

    public function test_a_file_that_is_not_a_pdf_is_rejected_at_the_first_chunk(): void
    {
        $token = $this->start(16)->json('token');

        $this->sendChunk($token, 0, 'not a pdf at all')->assertUnprocessable();
        $this->assertDatabaseCount('program_pdf_uploads', 0);
    }

    public function test_chunks_must_arrive_in_order(): void
    {
        $token = $this->start(12)->json('token');

        $this->sendChunk($token, 3, 'abc')->assertStatus(409);
        $this->sendChunk($token, 0, '%PDF-1')->assertOk();
        $this->sendChunk($token, 0, '%PDF-1')->assertStatus(409); // already received
        $this->sendChunk($token, 6, 'too many bytes')->assertStatus(409);
        $this->sendChunk($token, 6, 'abcdef')->assertOk();
    }

    public function test_an_unfinished_upload_cannot_be_saved(): void
    {
        $token = $this->start(12)->json('token');
        $this->sendChunk($token, 0, '%PDF-1')->assertOk();

        $this->post(route('admin.programs.store'), array_merge(Program::factory()->raw(), [
            'type' => 'convocation',
            'program_pdf_upload' => $token,
        ]))->assertSessionHasErrors('program_pdf_upload');

        $this->assertDatabaseCount('programs', 0);
        $this->assertTrue(ProgramPdfUpload::where('token', $token)->exists());
    }

    public function test_uploading_requires_an_admin(): void
    {
        auth()->logout();

        $this->start(10)->assertUnauthorized();
    }

    public function test_abandoned_uploads_and_their_parts_are_pruned(): void
    {
        $abandoned = $this->start(12)->json('token');
        $this->sendChunk($abandoned, 0, '%PDF-1')->assertOk();
        $recent = $this->start(12)->json('token');
        $this->sendChunk($recent, 0, '%PDF-1')->assertOk();
        ProgramPdfUpload::where('token', $abandoned)->toBase()->update(['updated_at' => now()->subDays(2)]);

        $this->artisan('model:prune', ['--model' => [ProgramPdfUpload::class]])->assertSuccessful();

        $this->assertFalse(ProgramPdfUpload::where('token', $abandoned)->exists());
        $this->assertTrue(ProgramPdfUpload::where('token', $recent)->exists());
        $this->assertSame(1, ProgramPdfPart::count(), 'Only the recent upload\'s part is left.');
    }

    public function test_deleting_a_program_removes_its_stored_parts(): void
    {
        $program = Program::factory()->create(['type' => 'convocation']);
        $this->put(route('admin.programs.update', $program), array_merge(Program::factory()->raw(), [
            'type' => 'convocation',
            'program_pdf_upload' => $this->upload('%PDF-1.4 to be deleted'),
        ]))->assertSessionHasNoErrors();
        $this->post(route('admin.programs.publish', $program))->assertSessionHasNoErrors();
        $this->assertSame(2, ProgramPdfPart::count());

        $this->delete(route('admin.programs.destroy', $program));

        $this->assertSame(0, ProgramPdfPart::count());
    }
}
