<?php

namespace Tests\Feature;

use App\Course;
use App\Discipline;
use App\Mail\ChecklistUploaded;
use App\Mail\NotifySetterAboutExternalComments;
use App\Mail\NotifySetterAboutModeratorComments;
use App\Mail\NotifyTeachingOfficeExternalHasCommented;
use App\Mail\PaperForRegistry;
use App\Paper;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Models\Activity;
use Tests\TenantTestCase;
use Tests\TestCase;

class PaperUploadTest extends TenantTestCase
{


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
    public function an_external_can_upload_thier_comments_which_triggers_an_email_to_the_setter_and_teaching_office_contact()
    {
        $this->withoutExceptionHandling();
        Mail::fake();
        Storage::fake('exampapers');

        option(['teaching_office_contact_glasgow' => 'jenny@example.com']);
        $setter = create(User::class);
        $discipline = create(Discipline::class, ['contact' => 'someone@example.com']);
        $course = create(Course::class, ['discipline_id' => $discipline->id]);
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
        Mail::assertQueued(NotifyTeachingOfficeExternalHasCommented::class, function ($mail) use ($discipline) {
            return $mail->hasTo($discipline->contact);
        });
    }

    /** @test */
    public function an_external_can_upload_thier_solution_comments_which_triggers_an_email_to_the_setter_and_teaching_office_contact()
    {
        $this->withoutExceptionHandling();
        Mail::fake();
        Storage::fake('exampapers');

        option(['teaching_office_contact_glasgow' => 'jenny@example.com']);
        $setter = create(User::class);
        $discipline = create(Discipline::class, ['contact' => 'someone@example.com']);
        $course = create(Course::class, ['discipline_id' => $discipline->id]);
        $setter->markAsSetter($course);
        $external = create(User::class);
        $external->markAsExternal($course);

        $response = $this->actingAs($external)->postJson(route('course.paper.store', $course->id), [
            'paper' => UploadedFile::fake()->create('main_paper_1.pdf', 1),
            'category' => 'main',
            'subcategory' => Paper::EXTERNAL_SOLUTION_COMMENTS,
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
        Mail::assertQueued(NotifyTeachingOfficeExternalHasCommented::class, function ($mail) use ($discipline) {
            return $mail->hasTo($discipline->contact);
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
        $discipline = create(Discipline::class, ['contact' => 'someone@example.com']);
        $course = create(Course::class, ['code' => 'ENG1234', 'discipline_id' => $discipline->id]);
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
        Mail::assertQueued(PaperForRegistry::class, function ($mail) use ($discipline) {
            return $mail->hasTo($discipline->contact);
        });
    }

    /** @test */
    public function uestc_courses_have_an_extra_category_of_uploads_of_resit2_which_doesnt_trigger_emails_even_if_it_was_set_to_the_paper_checklist()
    {
        Mail::fake();
        $this->withoutExceptionHandling();
        Storage::fake('exampapers');
        $staff = create(User::class);
        $course = create(Course::class, ['code' => 'UESTC1234']);
        $staff->markAsSetter($course);
        $moderator1 = create(User::class);
        $moderator2 = create(User::class);
        $moderator1->markAsModerator($course);
        $moderator2->markAsModerator($course);
        $file = UploadedFile::fake()->create('main_paper_1.pdf', 1);

        $response = $this->actingAs($staff)->postJson(route('course.paper.store', $course->id), [
            'paper' => $file,
            'category' => 'resit2',
            'subcategory' => 'flump',
            'comment' => 'Whatever',
        ]);

        $response->assertStatus(201);
        $this->assertCount(1, $course->papers);
        $this->assertCount(1, $course->papers->first()->comments);
        $paper = $course->papers->first();
        $this->assertEquals('resit2', $paper->category);

        // check an email wasn't sent to anyone about the new upload
        Mail::assertNothingSent();
        Mail::assertNothingQueued();
    }

    /** @test */
    public function people_not_associated_with_a_course_cant_upload_papers_for_it()
    {
        Mail::fake();
        Storage::fake('exampapers');

        $discipline = create(Discipline::class, ['contact' => 'someone@example.com']);
        $course = create(Course::class, ['code' => 'ENG1234', 'discipline_id' => $discipline->id]);
        $setter = create(User::class);
        $setter->markAsSetter($course);
        $otherSetter = create(User::class);

        $response = $this->actingAs($otherSetter)->postJson(route('course.paper.store', $course->id), [
            'paper' => UploadedFile::fake()->create('main_paper_1.pdf', 1),
            'category' => 'main',
            'subcategory' => Paper::PAPER_FOR_REGISTRY,
            'comment' => 'Whatever',
        ]);

        $response->assertStatus(403);
        $this->assertCount(0, $course->fresh()->papers);
    }

    /** @test */
    public function admins_can_upload_papers_to_any_course()
    {
        Mail::fake();
        Storage::fake('exampapers');

        $discipline = create(Discipline::class, ['contact' => 'someone@example.com']);
        $course = create(Course::class, ['code' => 'ENG1234', 'discipline_id' => $discipline->id]);
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->postJson(route('course.paper.store', $course->id), [
            'paper' => UploadedFile::fake()->create('main_paper_1.pdf', 1),
            'category' => 'main',
            'subcategory' => Paper::PAPER_FOR_REGISTRY,
            'comment' => 'Whatever',
        ]);

        $response->assertSuccessful();
        $this->assertCount(1, $course->fresh()->papers);
    }
}
