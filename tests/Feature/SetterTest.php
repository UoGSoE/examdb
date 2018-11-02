<?php

namespace Tests\Feature;

use App\User;
use App\Paper;
use App\Course;
use App\Solution;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SetterTest extends TestCase
{
    use RefreshDatabase;

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
        $mainSolution = create(Solution::class, ['course_id' => $course1->id, 'category' => 'main']);
        $resitSolution = create(Solution::class, ['course_id' => $course1->id, 'category' => 'resit']);

        $response = $this->actingAs($staff)->get(route('course.show', $course1->id));

        $response->assertSuccessful();
        $this->assertTrue($response->data('course')->is($course1));
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
    public function a_user_can_add_a_main_paper_and_comment_to_a_course()
    {
        $this->withoutExceptionHandling();
        Storage::fake('exampapers');
        $staff = create(User::class);
        $course = create(Course::class);
        $staff->markAsSetter($course);

        $response = $this->actingAs($staff)->postJson(route('course.paper.store', $course->id), [
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
    public function a_user_can_add_a_resit_paper_and_comment_to_a_course()
    {
        $this->withoutExceptionHandling();
        Storage::fake('exampapers');
        $staff = create(User::class);
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
    public function a_setter_can_approve_a_paper_for_a_course()
    {
        $this->withoutExceptionHandling();
        $user = create(User::class);
        $paper = create(Paper::class, ['category' => 'main']);
        $user->markAsSetter($paper->course);
        $this->assertFalse($paper->course->fresh()->isApprovedBySetter('main'));

        $response = $this->actingAs($user)->postJson(route('paper.approve', [$paper->course, 'main']));

        $response->assertStatus(200);
        $this->assertTrue($paper->course->fresh()->isApprovedBySetter('main'));

        // and check we recorded this in the activity/audit log
        tap(Activity::all()->last(), function ($log) use ($user, $paper) {
            $this->assertTrue($log->causer->is($user));
            $this->assertEquals(
                "Approved {$paper->category} paper for {$paper->course->code}",
                $log->description
            );
        });
    }

    /** @test */
    public function a_setter_can_unapprove_a_paper_for_a_course()
    {
        $this->withoutExceptionHandling();
        $user = create(User::class);
        $paper = create(Paper::class, ['category' => 'main']);
        $user->markAsSetter($paper->course);
        $paper->course->paperApprovedBy($user, 'main');
        $this->assertTrue($paper->course->fresh()->isApprovedBySetter('main'));

        $response = $this->actingAs($user)->postJson(route('paper.unapprove', [$paper->course, 'main']));

        $response->assertStatus(200);
        $this->assertFalse($paper->course->fresh()->isApprovedBySetter('main'));

        // and check we recorded this in the activity/audit log
        tap(Activity::all()->last(), function ($log) use ($user, $paper) {
            $this->assertTrue($log->causer->is($user));
            $this->assertEquals(
                "Unapproved {$paper->category} paper for {$paper->course->code}",
                $log->description
            );
        });
    }

    /** @test */
    public function a_setter_cant_approve_or_unapprove_of_a_paper_for_a_course_they_are_not_on()
    {
        $user = create(User::class);
        $paper = create(Paper::class);

        $response = $this->actingAs($user)->postJson(route('paper.unapprove', [$paper->course, 'main']));

        $response->assertStatus(403);

        $response = $this->actingAs($user)->postJson(route('paper.approve', [$paper->course, 'main']));

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
}
