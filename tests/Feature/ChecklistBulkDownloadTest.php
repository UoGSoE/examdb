<?php

namespace Tests\Feature;

use App\Exporters\ChecklistExporter;
use App\Jobs\BulkExportChecklists;
use App\Jobs\RemoveChecklistZip;
use App\Mail\ChecklistsReadyToDownload;
use App\Models\AcademicSession;
use App\Models\Course;
use App\Models\PaperChecklist;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

// fake the HTTP calls to the pdf printer api
class ChecklistBulkDownloadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        AcademicSession::createFirstSession();
    }

    /** @test */
    public function regular_users_cant_request_the_bulk_download_of_all_checklists()
    {
        Queue::fake();
        $user = create(User::class, ['is_admin' => false]);

        $response = $this->actingAs($user)->post(route('checklist.bulk_download'), ['area' => 'glasgow']);

        $response->assertForbidden();
        Queue::assertNothingPushed();
    }

    /** @test */
    public function admins_can_request_the_bulk_download_of_all_checklists()
    {
        $this->withoutExceptionHandling();
        Queue::fake();
        $admin = create(User::class, ['is_admin' => true]);

        $response = $this->actingAs($admin)->post(route('checklist.bulk_download'), ['area' => 'glasgow']);

        $response->assertRedirect();
        Queue::assertPushed(BulkExportChecklists::class);
    }

    /** @test */
    public function the_bulk_export_job_creates_a_zip_file_and_emails_the_person_who_requested_it()
    {
        Mail::fake();
        Http::fake([
            config('exampapers.pdf_api_url') => Http::response('whatever', 200),
        ]);
        Storage::fake('exampapers');
        Storage::fake('local');
        Queue::fake();
        $admin = create(User::class, ['is_admin' => true]);
        $glaCourse1 = create(Course::class, ['code' => 'ENG1234']);
        $glaCourse2 = create(Course::class, ['code' => 'ENG4567']);
        $uestcCourse1 = create(Course::class, ['code' => 'UESTC4567']);
        $checklist1 = create(PaperChecklist::class, ['course_id' => $glaCourse1->id, 'category' => 'main']);
        $checklist2 = create(PaperChecklist::class, ['course_id' => $glaCourse2->id, 'category' => 'resit']);
        $checklist3 = create(PaperChecklist::class, ['course_id' => $uestcCourse1->id, 'category' => 'resit']);

        BulkExportChecklists::dispatchNow($admin);

        Mail::assertQueued(ChecklistsReadyToDownload::class, 1);
        Mail::assertQueued(ChecklistsReadyToDownload::class, function ($mail) use ($admin) {
            return $mail->hasTo($admin->email) && ! is_null($mail->link);
        });
        Storage::disk('exampapers')->assertExists('checklists/checklists_'.$admin->id.'.zip');

        // this is just to assert that we didn't leave any temp files around - too lazy to write a specific test for it :-/
        $this->assertCount(0, Storage::disk('local')->files('checklists/'.$admin->id));
    }

    /** @test */
    public function only_the_person_who_requested_the_download_can_access_it()
    {
        $this->markTestSkipped('TODO - fix this up as the ChecklistExporter isnt used any more');

        Mail::fake();
        Storage::fake('exampapers');
        Queue::fake();
        $admin1 = create(User::class, ['is_admin' => true]);
        $admin2 = create(User::class, ['is_admin' => true]);
        create(PaperChecklist::class);

        login($admin1);

        $link = (new ChecklistExporter($admin1))->export();

        $response = $this->actingAs($admin2)->get($link);

        $response->assertStatus(401);
    }

    /** @test */
    public function a_job_is_queued_which_will_remove_the_zip_file()
    {
        Mail::fake();
        Http::fake([
            config('exampapers.pdf_api_url') => Http::response('whatever', 200),
        ]);
        Storage::fake('exampapers');
        Queue::fake();
        $admin = create(User::class, ['is_admin' => true]);
        $glaCourse1 = create(Course::class, ['code' => 'ENG1234']);
        $glaCourse2 = create(Course::class, ['code' => 'ENG4567']);
        $uestcCourse1 = create(Course::class, ['code' => 'UESTC4567']);
        $checklist1 = create(PaperChecklist::class, ['course_id' => $glaCourse1->id, 'category' => 'main']);
        $checklist2 = create(PaperChecklist::class, ['course_id' => $glaCourse2->id, 'category' => 'resit']);
        $checklist3 = create(PaperChecklist::class, ['course_id' => $uestcCourse1->id, 'category' => 'resit']);

        BulkExportChecklists::dispatchNow($admin);

        Queue::assertPushed(RemoveChecklistZip::class);
    }

    /** @test */
    public function the_remove_checklist_zip_job_does_remove_the_zip()
    {
        Mail::fake();
        Http::fake([
            config('exampapers.pdf_api_url') => Http::response('whatever', 200),
        ]);
        Storage::fake('exampapers');
        $admin = create(User::class, ['is_admin' => true]);
        $glaCourse1 = create(Course::class, ['code' => 'ENG1234']);
        $glaCourse2 = create(Course::class, ['code' => 'ENG4567']);
        $uestcCourse1 = create(Course::class, ['code' => 'UESTC4567']);
        $checklist1 = create(PaperChecklist::class, ['course_id' => $glaCourse1->id, 'category' => 'main']);
        $checklist2 = create(PaperChecklist::class, ['course_id' => $glaCourse2->id, 'category' => 'resit']);
        $checklist3 = create(PaperChecklist::class, ['course_id' => $uestcCourse1->id, 'category' => 'resit']);

        BulkExportChecklists::dispatchNow($admin);

        Storage::disk('exampapers')->assertMissing('checklists/checklists_'.$admin->id.'.zip');
    }
}
