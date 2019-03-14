<?php

namespace Tests\Feature;

use App\User;
use App\Paper;
use App\Course;
use App\Solution;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Foundation\Testing\WithFaker;
use App\Mail\NotifyLocalsAboutExternalComments;
use App\Mail\NotifySetterAboutExternalComments;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExternalsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_user_can_see_all_the_courses_they_are_an_external_for()
    {
        $staff = factory(User::class)->states('external')->create();
        $course1 = create(Course::class);
        $course2 = create(Course::class);
        $course3 = create(Course::class);
        $staff->markAsExternal($course1);
        $staff->markAsExternal($course2);

        $response = $this->actingAs($staff)->get(route('home'));

        $response->assertSuccessful();
        $this->assertCount(0, $response->data('setterCourses'));
        $this->assertCount(0, $response->data('moderatedCourses'));
        $this->assertCount(2, $response->data('externalCourses'));
        $this->assertTrue($response->data('externalCourses')->contains($course1));
        $this->assertTrue($response->data('externalCourses')->contains($course2));
        $this->assertFalse($response->data('externalCourses')->contains($course3));
        $response->assertSee($course1->code);
        $response->assertSee($course2->code);
        $response->assertDontSee($course3->code);
    }

    /** @test */
    public function a_user_can_see_the_page_for_an_individual_course_they_are_external_for()
    {
        $this->withoutExceptionHandling();
        $staff = factory(User::class)->states('external')->create();
        $course1 = create(Course::class);
        $staff->markAsExternal($course1);
        $mainPaper = create(Paper::class, ['course_id' => $course1->id, 'category' => 'main']);
        $resitPaper = create(Paper::class, ['course_id' => $course1->id, 'category' => 'resit']);
        $mainSolution = create(Solution::class, ['course_id' => $course1->id, 'category' => 'main']);
        $resitSolution = create(Solution::class, ['course_id' => $course1->id, 'category' => 'resit']);

        $response = $this->actingAs($staff)->get(route('course.show', $course1->id));

        $response->assertSuccessful();
        $this->assertTrue($response->data('course')->is($course1));
    }

    /** @test */
    public function a_user_cant_see_the_page_for_a_course_they_arent_involved_with()
    {
        $staff = factory(User::class)->states('external')->create();
        $course1 = create(Course::class);

        $response = $this->actingAs($staff)->get(route('course.show', $course1->id));

        $response->assertStatus(403);
    }

    /** @test */
    public function a_user_can_add_a_main_paper_and_comment_to_a_course()
    {
        $this->withoutExceptionHandling();
        Storage::fake('exampapers');
        Mail::fake();
        $external = factory(User::class)->states('external')->create();
        $course = create(Course::class);
        $external->markAsExternal($course);
        $setter1 = create(User::class);
        $setter1->markAsSetter($course);
        $setter2 = create(User::class);
        $setter2->markAsSetter($course);
        $moderator1 = create(User::class);
        $moderator2 = create(User::class);
        $moderator1->markAsModerator($course);
        $moderator2->markAsModerator($course);

        $response = $this->actingAs($external)->postJson(route('course.paper.store', $course->id), [
            'paper' => UploadedFile::fake()->create('main_paper_1.pdf', 1),
            'category' => 'main',
            'subcategory' => 'fred',
            'comment' => 'Whatever',
        ]);

        $response->assertStatus(201);
        $this->assertCount(1, $course->papers);
        $this->assertCount(1, $course->papers->first()->comments);
        Storage::disk('exampapers')->assertExists($course->papers->first()->filename);
        $paper = $course->papers->first();
        $this->assertEquals('main', $paper->category);
        $this->assertEquals('fred', $paper->subcategory);
        $this->assertEquals('Whatever', $paper->comments->first()->comment);
        $this->assertTrue($paper->user->is($external));
        $this->assertTrue($paper->course->is($course));

        // and check we recorded this in the activity/audit log
        tap(Activity::all()->last(), function ($log) use ($external, $paper) {
            $this->assertTrue($log->causer->is($external));
            $this->assertEquals(
                "Uploaded a paper ({$paper->course->code} - {$paper->category} / {$paper->subcategory})",
                $log->description
            );
        });

        // check an email was sent to each local setter about the new upload
        Mail::assertQueued(NotifySetterAboutExternalComments::class, 2);
        Mail::assertQueued(NotifySetterAboutExternalComments::class, function ($mail) use ($setter1) {
            return $mail->hasTo($setter1->email);
        });
        Mail::assertQueued(NotifySetterAboutExternalComments::class, function ($mail) use ($setter2) {
            return $mail->hasTo($setter2->email);
        });
    }

    /** @test */
    public function a_user_can_add_a_resit_paper_and_comment_to_a_course()
    {
        $this->withoutExceptionHandling();
        Storage::fake('exampapers');
        $staff = factory(User::class)->states('external')->create();
        $course = create(Course::class);
        $staff->markAsSetter($course);

        $response = $this->actingAs($staff)->postJson(route('course.paper.store', $course->id), [
            'paper' => UploadedFile::fake()->create('main_paper_1.pdf', 1),
            'category' => 'resit',
            'subcategory' => 'fred',
            'comment' => 'Whatever',
        ]);

        $response->assertStatus(201);
        $this->assertCount(1, $course->papers);
        $this->assertCount(1, $course->papers->first()->comments);
        Storage::disk('exampapers')->assertExists($course->papers->first()->filename);
        $paper = $course->papers->first();
        $this->assertEquals('resit', $paper->category);
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
    }

    /** @test */
    public function an_external_cant_approve_or_unapprove_any_papers()
    {
        $staff = factory(User::class)->states('external')->create();
        $paper = create(Paper::class);
        $staff->markAsExternal($paper->course);

        $response = $this->actingAs($staff)->postJson(route('paper.unapprove', [$paper->course, 'main']));

        $response->assertStatus(403);

        $response = $this->actingAs($staff)->postJson(route('paper.approve', [$paper->course, 'main']));

        $response->assertStatus(403);
    }

    /** @test */
    public function an_external_cant_delete_any_papers()
    {
        Storage::fake('exampapers');
        $staff = factory(User::class)->states('external')->create();
        $paper = create(Paper::class);
        $staff->markAsExternal($paper->course);
        Storage::disk('exampapers')->put($paper->filename, 'hello');
        $this->assertTrue(Storage::disk('exampapers')->exists($paper->filename));

        $response = $this->actingAs($staff)->deleteJson(route('paper.delete', $paper));

        $response->assertStatus(403);
        $this->assertDatabaseHas('papers', ['id' => $paper->id]);
        $this->assertTrue(Storage::disk('exampapers')->exists($paper->filename));
    }

    /** @test */
    public function an_external_can_download_any_paper_for_a_course_they_are_on()
    {
        $this->withoutExceptionHandling();
        Storage::fake('exampapers');
        $user = create(User::class);
        $paper = create(Paper::class, ['user_id' => $user->id]);
        $user->markAsExternal($paper->course);
        Storage::disk('exampapers')->put($paper->filename, encrypt('hello'));

        $response = $this->actingAs($user)->get(route('paper.show', $paper));

        $response->assertStatus(200);

        // and check we recorded this in the activity/audit log
        tap(Activity::all()->last(), function ($log) use ($user, $paper) {
            $this->assertTrue($log->causer->is($user));
            $this->assertEquals(
                "Downloaded {$paper->category} paper '{$paper->original_filename}' for {$paper->course->code}",
                $log->description
            );
        });
    }

    /** @test */
    public function an_external_cant_download_any_paper_for_a_course_they_are_not_on()
    {
        Storage::fake('exampapers');
        $user = create(User::class);
        $paper = create(Paper::class, ['user_id' => $user->id]);
        Storage::disk('exampapers')->put($paper->filename, encrypt('hello'));

        $response = $this->actingAs($user)->get(route('paper.show', $paper));

        $response->assertStatus(403);
    }
}
