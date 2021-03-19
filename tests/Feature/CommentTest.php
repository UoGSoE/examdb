<?php

namespace Tests\Feature;

use App\Course;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Models\Activity;
use Tests\TenantTestCase;
use Tests\TestCase;

class CommentTest extends TenantTestCase
{
    use RefreshDatabase;

    /** @test */
    public function users_can_add_a_comment_to_courses_they_are_involved_with_without_needing_a_paper()
    {
        Mail::fake();
        $this->withoutExceptionHandling();
        Storage::fake('exampapers');
        $staff = create(User::class);
        $course = create(Course::class);
        $staff->markAsSetter($course);

        $response = $this->actingAs($staff)->postJson(route('course.comment.store', $course->id), [
            'category' => 'main',
            'comment' => 'Whatever',
        ]);

        $response->assertStatus(201);
        $this->assertCount(1, $course->papers);
        $this->assertCount(1, $course->papers->first()->comments);
        Storage::disk('exampapers')->assertExists($course->papers->first()->filename);
        $paper = $course->papers->first();
        $this->assertEquals('main', $paper->category);
        $this->assertEquals('comment', $paper->subcategory);
        $this->assertEquals('Whatever', $paper->comments->first()->comment);
        $this->assertTrue($paper->user->is($staff));
        $this->assertTrue($paper->course->is($course));

        // and check we recorded this in the activity/audit log
        tap(Activity::all()->last(), function ($log) use ($staff, $paper) {
            $this->assertTrue($log->causer->is($staff));
            $this->assertEquals(
                "Added a comment ({$paper->course->code} - {$paper->category} / {$paper->subcategory})",
                $log->description
            );
        });
    }
}
