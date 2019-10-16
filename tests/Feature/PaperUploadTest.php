<?php

namespace Tests\Feature;

use App\User;
use App\Paper;
use App\Course;
use Tests\TestCase;
use App\Mail\ChecklistUploaded;
use Illuminate\Http\UploadedFile;
use App\Mail\NotifyTeachingOffice;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Foundation\Testing\WithFaker;
use App\Mail\NotifySetterAboutExternalComments;
use App\Mail\NotifySetterAboutModeratorComments;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Mail\NotifyTeachingOfficeExternalHasCommented;

class PaperUploadTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_setter_can_upload_a_paper()
    {
        Mail::fake();
        $this->withoutExceptionHandling();
        Storage::fake('exampapers');
        $staff = create(User::class);
        $course = create(Course::class);
        $staff->markAsSetter($course);
        $moderator1 = create(User::class);
        $moderator2 = create(User::class);
        $moderator1->markAsModerator($course);
        $moderator2->markAsModerator($course);
        $file = UploadedFile::fake()->create('main_paper_1.pdf', 1);

        $response = $this->actingAs($staff)->postJson(route('course.paper.store', $course->id), [
            'paper' => $file,
            'category' => 'main',
            'subcategory' => 'fred',
            'comment' => 'Whatever',
        ]);

        $response->assertStatus(201);
        $this->assertCount(1, $course->papers);
        $this->assertCount(1, $course->papers->first()->comments);
        Storage::disk('exampapers')->assertExists($course->papers->first()->filename);
        $this->assertNotEquals( // basic check to check the file was encrypted
            md5($file->get()),
            md5(Storage::disk('exampapers')->get($course->papers->first()->filename))
        );
        $paper = $course->papers->first();
        $this->assertEquals('main', $paper->category);
        $this->assertEquals('fred', $paper->subcategory);
        $this->assertEquals('Whatever', $paper->comments->first()->comment);
        $this->assertTrue($paper->user->is($staff));
        $this->assertTrue($paper->course->is($course));

        // and check we recorded this in the activity/audit log
        tap(Activity::all()->last(), function ($log) use ($staff, $paper) {
            $this->assertTrue($log->causer->is($staff));
            $this->assertEquals(
                "Uploaded a paper ({$paper->course->code} - {$paper->category} / {$paper->subcategory})",
                $log->description
            );
        });

        // check an email wasn't sent to any moderator about the new upload
        Mail::assertNothingSent();
        Mail::assertNothingQueued();
    }

    /** @test */
    public function when_the_setter_uploads_the_paper_checklist_a_mail_is_triggered_to_the_moderators()
    {
        Mail::fake();
        $this->withoutExceptionHandling();
        Storage::fake('exampapers');
        $staff = create(User::class);
        $course = create(Course::class);
        $staff->markAsSetter($course);
        $moderator1 = create(User::class);
        $moderator2 = create(User::class);
        $moderator1->markAsModerator($course);
        $moderator2->markAsModerator($course);

        $response = $this->actingAs($staff)->postJson(route('course.paper.store', $course->id), [
            'paper' => UploadedFile::fake()->create('main_paper_1.pdf', 1),
            'category' => 'main',
            'subcategory' => Paper::PAPER_CHECKLIST,
            'comment' => 'Whatever',
        ]);

        $response->assertStatus(201);
        $this->assertCount(1, $course->papers);
        $this->assertCount(1, $course->papers->first()->comments);
        $paper = $course->papers->first();
        Storage::disk('exampapers')->assertExists($paper->filename);
        $this->assertEquals('main', $paper->category);
        $this->assertEquals(Paper::PAPER_CHECKLIST, $paper->subcategory);
        $this->assertEquals('Whatever', $paper->comments->first()->comment);
        $this->assertTrue($paper->user->is($staff));
        $this->assertTrue($paper->course->is($course));

        // and check we recorded this in the activity/audit log
        tap(Activity::all()->last(), function ($log) use ($staff, $paper) {
            $this->assertTrue($log->causer->is($staff));
            $this->assertEquals(
                "Uploaded a paper ({$paper->course->code} - {$paper->category} / {$paper->subcategory})",
                $log->description
            );
        });

        // check an email was sent to all the course moderators about the new upload
        Mail::assertQueued(ChecklistUploaded::class, 2);
    }

    /** @test */
    public function a_moderator_can_upload_thier_checklist_which_triggers_an_email_to_the_setter()
    {
        Mail::fake();
        $this->withoutExceptionHandling();
        Storage::fake('exampapers');
        $setter = create(User::class);
        $course = create(Course::class);
        $setter->markAsSetter($course);
        $moderator = create(User::class);
        $moderator->markAsModerator($course);

        $response = $this->actingAs($moderator)->postJson(route('course.paper.store', $course->id), [
            'paper' => UploadedFile::fake()->create('main_paper_1.pdf', 1),
            'category' => 'main',
            'subcategory' => Paper::PAPER_CHECKLIST,
            'comment' => 'Whatever',
        ]);

        $response->assertStatus(201);
        $this->assertCount(1, $course->papers);
        $this->assertCount(1, $course->papers->first()->comments);
        $paper = $course->papers->first();
        // and check we recorded this in the activity/audit log
        tap(Activity::all()->last(), function ($log) use ($moderator, $paper) {
            $this->assertTrue($log->causer->is($moderator));
            $this->assertEquals(
                "Uploaded a paper ({$paper->course->code} - {$paper->category} / {$paper->subcategory})",
                $log->description
            );
        });

        // check an email was sent to all the course setters about the new upload
        Mail::assertQueued(NotifySetterAboutModeratorComments::class, function ($mail) use ($setter) {
            return $mail->hasTo($setter->email);
        });
    }

    /** @test */
    public function an_external_can_upload_thier_comments_which_triggers_an_email_to_the_setter_and_teaching_office()
    {
        $this->withoutExceptionHandling();
        Mail::fake();
        Storage::fake('exampapers');

        option(['teaching_office_contact_glasgow' => 'jenny@example.com']);
        $setter = create(User::class);
        $course = create(Course::class);
        $setter->markAsSetter($course);
        $external = create(User::class);
        $external->markAsExternal($course);

        $response = $this->actingAs($external)->postJson(route('course.paper.store', $course->id), [
            'paper' => UploadedFile::fake()->create('main_paper_1.pdf', 1),
            'category' => 'main',
            'subcategory' => Paper::EXTERNAL_COMMENTS,
            'comment' => 'Whatever',
        ]);

        $response->assertStatus(201);
        $this->assertCount(1, $course->papers);
        $this->assertCount(1, $course->papers->first()->comments);
        $paper = $course->papers->first();
        // and check we recorded this in the activity/audit log
        tap(Activity::all()->last(), function ($log) use ($external, $paper) {
            $this->assertTrue($log->causer->is($external));
            $this->assertEquals(
                "Uploaded a paper ({$paper->course->code} - {$paper->category} / {$paper->subcategory})",
                $log->description
            );
        });

        // check an email was sent to all the course setter about the new upload
        Mail::assertQueued(NotifySetterAboutExternalComments::class, function ($mail) use ($setter) {
            return $mail->hasTo($setter->email);
        });
        // check an email was sent to the teaching office about the new upload
        Mail::assertQueued(NotifyTeachingOfficeExternalHasCommented::class, function ($mail) {
            return $mail->hasTo(option('teaching_office_contact_glasgow'));
        });
    }

    /** @test */
    public function when_the_setter_uploads_the_paper_for_registry_an_email_is_sent_to_teaching_office_contact()
    {
        $this->withoutExceptionHandling();
        Mail::fake();
        Storage::fake('exampapers');

        option(['teaching_office_contact_glasgow' => 'jenny@example.com']);
        $setter = create(User::class);
        $course = create(Course::class, ['code' => 'ENG1234']);
        $setter->markAsSetter($course);

        $response = $this->actingAs($setter)->postJson(route('course.paper.store', $course->id), [
            'paper' => UploadedFile::fake()->create('main_paper_1.pdf', 1),
            'category' => 'main',
            'subcategory' => Paper::PAPER_FOR_REGISTRY,
            'comment' => 'Whatever',
        ]);

        $response->assertStatus(201);
        $this->assertCount(1, $course->papers);
        $this->assertCount(1, $course->papers->first()->comments);
        $paper = $course->papers->first();

        // check an email was sent to all the course moderators about the new upload
        Mail::assertQueued(NotifyTeachingOffice::class, function ($mail) {
            return $mail->hasTo(option('teaching_office_contact_glasgow'));
        });
    }
}
