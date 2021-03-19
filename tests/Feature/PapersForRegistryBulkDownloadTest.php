<?php

namespace Tests\Feature;

use App\Course;
use App\Exporters\PaperExporter;
use App\Http\Controllers\Admin\ExportPapersForRegistryController;
use App\Jobs\ExportPapersForRegistry;
use App\Jobs\RemoveRegistryZip;
use App\Mail\RegistryPapersExported;
use App\Paper;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Models\Activity;
use Tests\TenantTestCase;
use Tests\TestCase;
use ZipArchive;

class PapersForRegistryBulkDownloadTest extends TenantTestCase
{
    use RefreshDatabase;

    /** @test */
    public function regular_users_cant_do_the_export()
    {
        Queue::fake();
        Storage::fake('exampapers');
        $user = create(User::class);

        $response = $this->actingAs($user)->postJson(route('export.paper.registry'));

        $response->assertStatus(403);
        Queue::assertNotPushed(ExportPapersForRegistry::class);
    }

    /** @test */
    public function an_admin_can_kick_off_an_export_of_the_papers_for_registry()
    {
        $this->withoutExceptionHandling();
        Mail::fake();
        Storage::fake('exampapers');
        Queue::fake();
        $admin = create(User::class, ['is_admin' => true]);

        $response = $this->actingAs($admin)->postJson(route('export.paper.registry'));

        $response->assertOk();
        Queue::assertPushedOn('long-running-queue', ExportPapersForRegistry::class);
        tap(Activity::all()->last(), function ($log) use ($admin) {
            $this->assertTrue($log->causer->is($admin));
            $this->assertEquals('Created a ZIP of the papers for registry', $log->description);
        });
    }

    /** @test */
    public function the_admin_is_emailed_a_link_to_download_the_zip_of_papers_once_they_are_ready()
    {
        $this->withoutExceptionHandling();
        Mail::fake();
        Storage::fake('exampapers');
        $admin = create(User::class, ['is_admin' => true]);

        $response = $this->actingAs($admin)->postJson(route('export.paper.registry'));

        $response->assertOk();
        Mail::assertQueued(RegistryPapersExported::class, function ($mail) use ($admin) {
            return $mail->hasTo($admin->email) && $mail->link != null;
        });
    }

    /** @test */
    public function running_the_export_job_creates_a_zip_on_the_exampapers_disk_and_leaves_no_temp_files_around()
    {
        $this->withoutExceptionHandling();
        Mail::fake();
        Storage::fake('exampapers');
        Queue::fake();
        // pre-remove any temp files left if this test fails
        $tempFiles = glob(sys_get_temp_dir().'/'.config('exampapers.registry_temp_file_prefix').'*');
        foreach ($tempFiles as $filename) {
            unlink($filename);
        }
        $admin = create(User::class, ['is_admin' => true]);

        ExportPapersForRegistry::dispatchNow($admin);

        Storage::disk('exampapers')->assertExists('registry/papers_'.$admin->id.'.zip');
        $this->assertEmpty(glob(sys_get_temp_dir().'/'.config('exampapers.registry_temp_file_prefix').'*'));
    }

    /** @test */
    public function the_download_link_will_let_the_admin_get_the_zip_of_all_registry_papers()
    {
        $this->withoutExceptionHandling();
        Mail::fake();
        Storage::fake('exampapers');
        Queue::fake();
        $admin = create(User::class, ['is_admin' => true]);
        login($admin);
        $course1 = create(Course::class);
        $course2 = create(Course::class);
        $course1->addPaper('main', Paper::PAPER_FOR_REGISTRY, UploadedFile::fake()->create('document1.pdf', 1));
        $course1->addPaper('resit', Paper::PAPER_FOR_REGISTRY, UploadedFile::fake()->create('document2.pdf', 1));
        $course2->addPaper('main', Paper::PAPER_FOR_REGISTRY, UploadedFile::fake()->create('document4.pdf', 1));
        $course2->addPaper('main', 'flump', UploadedFile::fake()->create('document4.pdf', 1));

        $link = (new PaperExporter(Paper::PAPER_FOR_REGISTRY, $admin))->export();

        $response = $this->actingAs($admin)->get($link);

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/zip');
        Storage::put('temp.zip', $response->streamedContent());
        $zip = new ZipArchive();
        $res = $zip->open(Storage::path('temp.zip'));
        $filenames = collect([]);
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);
            $filenames->push(basename($stat['name']));
        }
        $this->assertCount(4, $filenames); // only 3 registry papers, shouldn't include the 'flump' one - but there is always a placeholder file
        $this->assertTrue($filenames->contains($course1->code.'_Main_document1.pdf'));
        $this->assertTrue($filenames->contains($course1->code.'_Resit_document2.pdf'));
        $this->assertTrue($filenames->contains($course2->code.'_Main_document4.pdf'));
        $this->assertFalse($filenames->contains($course2->code.'_Main_document3.pdf'));
        tap(Activity::all()->last(), function ($log) use ($admin) {
            $this->assertTrue($log->causer->is($admin));
            $this->assertEquals('Downloaded papers for registry ZIP', $log->description);
        });
    }

    /** @test */
    public function tampered_download_links_dont_work()
    {
        Mail::fake();
        Storage::fake('exampapers');
        Queue::fake();
        $admin1 = create(User::class, ['is_admin' => true]);
        login($admin1);
        $course1 = create(Course::class);
        $course2 = create(Course::class);
        $course1->addPaper('main', Paper::PAPER_FOR_REGISTRY, UploadedFile::fake()->create('document1.pdf', 1));
        $course1->addPaper('resit', Paper::PAPER_FOR_REGISTRY, UploadedFile::fake()->create('document2.pdf', 1));
        $course2->addPaper('main', 'flump', UploadedFile::fake()->create('document3.pdf', 1));
        $course2->addPaper('main', Paper::PAPER_FOR_REGISTRY, UploadedFile::fake()->create('document4.pdf', 1));

        $link = (new PaperExporter(Paper::PAPER_FOR_REGISTRY, $admin1))->export();

        $response = $this->actingAs($admin1)->get($link.'xyz');

        $response->assertStatus(401);
    }

    /** @test */
    public function only_the_user_who_requested_the_download_can_access_it()
    {
        Mail::fake();
        Storage::fake('exampapers');
        Queue::fake();
        $admin1 = create(User::class, ['is_admin' => true]);
        $admin2 = create(User::class, ['is_admin' => true]);
        login($admin1);
        $course1 = create(Course::class);
        $course2 = create(Course::class);
        $course1->addPaper('main', Paper::PAPER_FOR_REGISTRY, UploadedFile::fake()->create('document1.pdf', 1));
        $course1->addPaper('resit', Paper::PAPER_FOR_REGISTRY, UploadedFile::fake()->create('document2.pdf', 1));
        $course2->addPaper('main', 'flump', UploadedFile::fake()->create('document3.pdf', 1));
        $course2->addPaper('main', Paper::PAPER_FOR_REGISTRY, UploadedFile::fake()->create('document4.pdf', 1));

        $link = (new PaperExporter(Paper::PAPER_FOR_REGISTRY, $admin1))->export();

        $response = $this->actingAs($admin2)->get($link);

        $response->assertStatus(401);
    }

    /** @test */
    public function when_the_zip_is_generated_a_job_is_queued_to_remove_it_again()
    {
        $this->withoutExceptionHandling();
        Mail::fake();
        Storage::fake('exampapers');
        Queue::fake();
        config(['exampapers.zip_expire_hours' => 8]);
        $admin = create(User::class, ['is_admin' => true]);

        $link = (new PaperExporter(Paper::PAPER_FOR_REGISTRY, $admin))->export();

        Queue::assertPushed(RemoveRegistryZip::class, function ($job) {
            return $job->filename != null && $job->delay->gt(
                now()->addHours(config('exampapers.zip_expire_hours'))->subMinutes(5)
            );
        });
    }

    /** @test */
    public function the_queued_job_does_remove_the_file()
    {
        $this->withoutExceptionHandling();
        Mail::fake();
        Storage::fake('exampapers');
        Queue::fake();
        config(['exampapers.zip_expire_hours' => 8]);
        $admin = create(User::class, ['is_admin' => true]);
        Storage::disk('exampapers')->put('test.zip', 'hello');
        Storage::disk('exampapers')->assertExists('test.zip');

        RemoveRegistryZip::dispatchNow('test.zip');

        Storage::disk('exampapers')->assertMissing('test.zip');
        tap(Activity::all()->last(), function ($log) {
            $this->assertEquals('Automatically removed registry zip test.zip', $log->description);
        });
    }
}
