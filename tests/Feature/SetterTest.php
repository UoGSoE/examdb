<?php

namespace Tests\Feature;

use App\Course;
use App\Mail\NotifyModeratorAboutApproval;
use App\Mail\NotifyModeratorAboutUnapproval;
use App\Paper;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Models\Activity;
use Tests\TenantTestCase;
use Tests\TestCase;

class SetterTest extends TenantTestCase
{


    /** @test */
    public function a_user_can_see_all_the_courses_they_are_a_setter_for()
    {
        $staff = create(User::class);
        $course1 = create(Course::class);
        $course2 = create(Course::class);
        $course3 = create(Course::class);
        $staff->markAsSetter($course1);
        $staff->markAsSetter($course2);

        $response = $this->actingAs($staff)->get(route('home'));

        $response->assertSuccessful();
        $this->assertCount(2, $response->data('setterCourses'));
        $this->assertTrue($response->data('setterCourses')->contains($course1));
        $this->assertTrue($response->data('setterCourses')->contains($course2));
        $this->assertFalse($response->data('setterCourses')->contains($course3));
        $this->assertCount(0, $response->data('moderatedCourses'));
        $this->assertCount(0, $response->data('externalCourses'));
        $response->assertSee($course1->code);
        $response->assertSee($course2->code);
        $response->assertDontSee($course3->code);
    }

    /** @test */
    public function a_user_can_see_the_page_for_an_individual_course_they_are_setter_for()
    {
        $this->withoutExceptionHandling();
        $staff = create(User::class);
        $course1 = create(Course::class);
        $staff->markAsSetter($course1);
        $mainPaper = create(Paper::class, ['course_id' => $course1->id, 'category' => 'main']);
        $resitPaper = create(Paper::class, ['course_id' => $course1->id, 'category' => 'resit']);
        $randomPaper = create(Paper::class);

        $response = $this->actingAs($staff)->get(route('course.show', $course1->id));

        $response->assertSuccessful();
        $this->assertTrue($response->data('course')->is($course1));
        $this->assertTrue($response->data('course')->papers->contains($mainPaper));
        $this->assertTrue($response->data('course')->papers->contains($resitPaper));
        $this->assertFalse($response->data('course')->papers->contains($randomPaper));
    }

    /** @test */
    public function archived_papers_show_up_in_the_course_view()
    {
        $this->withoutExceptionHandling();
        $staff = create(User::class);
        $course1 = create(Course::class);
        $staff->markAsSetter($course1);
        $archivedPaper = create(Paper::class, ['course_id' => $course1->id]);
        $archivedPaper->archive();
        $archivedCommentPaper = create(Paper::class, ['course_id' => $course1->id, 'subcategory' => Paper::COMMENT_SUBCATEGORY]);
        $archivedCommentPaper->archive();

        $response = $this->actingAs($staff)->get(route('course.show', $course1->id));

        $response->assertSuccessful();
        $this->assertTrue($response->data('course')->is($course1));
        // archived comments shouldn't show up in the list of archives
        $this->assertCount(1, $response->data('archivedPapers'));
        $this->assertTrue($response->data('archivedPapers')->contains($archivedPaper));
    }

    /** @test */
    public function hidden_papers_dont_show_up_in_the_course_view()
    {
        $this->withoutExceptionHandling();
        $staff = create(User::class);
        $course1 = create(Course::class);
        $staff->markAsSetter($course1);
        $mainPaper = create(Paper::class, ['course_id' => $course1->id, 'category' => 'main']);
        $resitPaper = create(Paper::class, ['course_id' => $course1->id, 'category' => 'resit']);
        $hiddenPaper = create(Paper::class, ['course_id' => $course1->id, 'is_hidden' => true]);


        $response = $this->actingAs($staff)->get(route('course.show', $course1->id));

        $response->assertSuccessful();
        $this->assertTrue($response->data('course')->is($course1));
        $this->assertTrue($response->data('course')->papers->contains($mainPaper));
        $this->assertTrue($response->data('course')->papers->contains($resitPaper));
        $this->assertFalse($response->data('course')->papers->contains($hiddenPaper));
    }

    /** @test */
    public function a_user_cant_see_the_page_for_a_course_they_arent_involved_with()
    {
        $staff = create(User::class);
        $course1 = create(Course::class);

        $response = $this->actingAs($staff)->get(route('course.show', $course1->id));

        $response->assertStatus(403);
    }

    /** @test */
    public function a_setter_can_delete_their_own_paper()
    {
        Storage::fake('exampapers');
        $user = create(User::class);
        $paper = create(Paper::class, ['user_id' => $user->id]);
        Storage::disk('exampapers')->put($paper->filename, 'hello');
        $this->assertTrue(Storage::disk('exampapers')->exists($paper->filename));

        $response = $this->actingAs($user)->deleteJson(route('paper.delete', $paper));

        $response->assertStatus(200);
        $this->assertDatabaseMissing('papers', ['id' => $paper->id]);
        $this->assertFalse(Storage::disk('exampapers')->exists($paper->filename));

        // and check we recorded this in the activity/audit log
        tap(Activity::all()->last(), function ($log) use ($user, $paper) {
            $this->assertTrue($log->causer->is($user));
            $this->assertEquals(
                "Deleted {$paper->category} paper '{$paper->original_filename}' for {$paper->course->code}",
                $log->description
            );
        });
    }

    /** @test */
    public function a_setter_cant_delete_someone_elses_paper()
    {
        $user = create(User::class);
        $paper = create(Paper::class);

        $response = $this->actingAs($user)->deleteJson(route('paper.delete', $paper));

        $response->assertStatus(403);
        $this->assertDatabaseHas('papers', ['id' => $paper->id]);
    }

    /** @test */
    public function a_setter_cant_delete_a_paper_after_some_limited_time()
    {
        Storage::fake('exampapers');
        config(['exampapers.delete_paper_limit_minutes' => 5]);
        $user = create(User::class);
        $paper = create(Paper::class, ['user_id' => $user->id, 'created_at' => now()->subMinutes(10)]);

        $response = $this->actingAs($user)->deleteJson(route('paper.delete', $paper));

        $response->assertStatus(403);
        $this->assertDatabaseHas('papers', ['id' => $paper->id]);
    }

    /** @test */
    public function a_setter_can_download_any_paper_for_a_course_they_are_on()
    {
        $this->withoutExceptionHandling();
        Storage::fake('exampapers');
        $user = create(User::class);
        $paper = create(Paper::class, ['user_id' => $user->id]);
        $user->markAsSetter($paper->course);
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
    public function a_setter_cant_download_any_paper_for_a_course_they_are_not_on()
    {
        Storage::fake('exampapers');
        $user = create(User::class);
        $paper = create(Paper::class, ['user_id' => $user->id]);
        Storage::disk('exampapers')->put($paper->filename, encrypt('hello'));

        $response = $this->actingAs($user)->get(route('paper.show', $paper));

        $response->assertStatus(403);
    }
}
