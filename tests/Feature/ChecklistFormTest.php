<?php

namespace Tests\Feature;

use App\User;
use App\Course;
use Tests\TestCase;
use Livewire\Livewire;
use App\PaperChecklist;
use App\Mail\ChecklistUpdated;
use Illuminate\Support\Facades\Mail;
use App\Mail\ExternalHasUpdatedTheChecklist;
use Illuminate\Foundation\Testing\WithFaker;
use App\Mail\ModeratorHasUpdatedTheChecklist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\Livewire\PaperChecklist as LivewirePaperChecklist;

class ChecklistFormTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function people_associated_with_a_course_can_see_the_form_to_create_a_checklist()
    {
        $this->withoutExceptionHandling();
        $user = create(User::class);
        $course = create(Course::class);
        $user->markAsSetter($course);

        $response = $this->actingAs($user)->get(route('course.checklist.create', [
            'course' => $course->id,
            'category' => 'main',
        ]));

        $response->assertOk();
        $response->assertViewHas('checklist');
        $response->assertViewHas('course', $course);
        $response->assertViewHas('category', 'main');
    }

    /** @test */
    public function people_not_associated_with_a_course_cant_see_create_a_checklist()
    {
        $user = create(User::class);
        $course = create(Course::class);

        $response = $this->actingAs($user)->get(route('course.checklist.create', [
            'course' => $course->id,
            'category' => 'main',
        ]));

        $response->assertStatus(403);
    }

    /** @test */
    public function people_associated_with_a_course_can_create_a_first_checklist()
    {
        $this->withoutExceptionHandling();
        $user = create(User::class);
        $course = create(Course::class);
        $user->markAsSetter($course);

        $response = $this->actingAs($user)->post(route('course.checklist.store', $course->id), [
            'course_id' => $course->id,
            'category' => 'main',
            'q1' => 'hello',
            'q2' => 'there',
        ]);

        $response->assertRedirect(route('course.show', $course->id));
        $response->assertSessionHasNoErrors();
        tap(PaperChecklist::first(), function ($checklist) use ($course, $user) {
            $this->assertEquals($checklist->version, PaperChecklist::CURRENT_VERSION);
            $this->assertTrue($checklist->course->is($course));
            $this->assertTrue($checklist->user->is($user));
            $this->assertEquals('main', $checklist->category);
            $this->assertEquals('hello', $checklist->q1);
            $this->assertEquals('there', $checklist->q2);
        });
    }

    /** @test */
    public function people_not_associated_with_a_course_cant_create_a_first_checklist()
    {
        $user = create(User::class);
        $course = create(Course::class);

        $response = $this->actingAs($user)->post(route('course.checklist.store', $course->id), [
            'course_id' => $course->id,
            'category' => 'main',
            'q1' => 'hello',
            'q2' => 'there',
        ]);

        $response->assertStatus(403);
        $this->assertEquals(0, PaperChecklist::count());
    }

    /** @test */
    public function if_there_is_already_a_checklist_for_this_course_and_user_and_category_its_values_are_used_in_the_form()
    {
        $this->withoutExceptionHandling();
        $user = create(User::class);
        $course = create(Course::class);
        $user->markAsSetter($course);
        $existingChecklist = create(PaperChecklist::class, [
            'course_id' => $course->id,
            'category' => 'main',
        ]);

        $response = $this->actingAs($user)->get(route('course.checklist.create', [
            'course' => $course->id,
            'category' => 'main',
        ]));

        $response->assertOk();
        $response->assertViewHas('checklist');
        $this->assertEquals($course->id, $response->data('checklist')->course_id);
        $this->assertEquals($existingChecklist->category, $response->data('checklist')->category);
        $this->assertEquals($existingChecklist->version, $response->data('checklist')->version);
        $this->assertEquals($existingChecklist->q1, $response->data('checklist')->q1);
        $this->assertEquals($existingChecklist->q2, $response->data('checklist')->q2);
    }

    /** @test */
    public function users_can_see_previous_read_only_versions_of_checklists()
    {
        $this->withoutExceptionHandling();
        $user = create(User::class);
        $course = create(Course::class);
        $user->markAsSetter($course);
        $existingChecklist1 = create(PaperChecklist::class, [
            'course_id' => $course->id,
            'category' => 'main',
        ]);
        $existingChecklist2 = create(PaperChecklist::class, [
            'course_id' => $course->id,
            'category' => 'main',
        ]);

        $response = $this->actingAs($user)->get(route('course.checklist.show', [
            'checklist' => $existingChecklist1->id,
        ]));

        $response->assertOk();
        $response->assertViewHas('checklist');
        $this->assertEquals($course->id, $response->data('checklist')->course_id);
        $this->assertEquals($existingChecklist1->category, $response->data('checklist')->category);
        $this->assertEquals($existingChecklist1->version, $response->data('checklist')->version);
        $this->assertEquals($existingChecklist1->q1, $response->data('checklist')->q1);
        $this->assertEquals($existingChecklist1->q2, $response->data('checklist')->q2);
    }

    /** @test */
    public function test_we_can_get_previous_and_next_checklists()
    {
        $course1 = create(Course::class);
        $course2 = create(Course::class);
        $existingChecklist1 = create(PaperChecklist::class, [
            'course_id' => $course1->id,
            'category' => 'main',
        ]);
        $existingChecklist2 = create(PaperChecklist::class, [
            'course_id' => $course1->id,
            'category' => 'main',
        ]);
        $existingChecklist3 = create(PaperChecklist::class, [
            'course_id' => $course1->id,
            'category' => 'main',
        ]);
        $existingChecklist4 = create(PaperChecklist::class, [
            'course_id' => $course1->id,
            'category' => 'resit',
        ]);

        $this->assertEquals($existingChecklist1->id, $existingChecklist2->getPreviousChecklist());
        $this->assertEquals($existingChecklist3->id, $existingChecklist2->getNextChecklist());
        $this->assertNull($existingChecklist3->getNextChecklist());
        $this->assertNull($existingChecklist1->getPreviousChecklist());
        $this->assertNull($existingChecklist4->getNextChecklist());
        $this->assertNull($existingChecklist4->getPreviousChecklist());
    }

    /** @test */
    public function we_can_download_a_pdf_of_the_paper_checklist()
    {
        $this->withoutExceptionHandling();
        $user = create(User::class);
        $course = create(Course::class);
        $user->markAsSetter($course);
        $existingChecklist1 = create(PaperChecklist::class, [
            'course_id' => $course->id,
            'category' => 'main',
        ]);

        $response = $this->actingAs($user)->get(route('course.checklist.pdf', [
            'checklist' => $existingChecklist1->id,
        ]));

        $response->assertOk();
        $this->assertEquals(
            $response->headers->get('content-disposition'),
            'attachment; filename="'.$course->code.'_paper_checklist.pdf"'
        );
    }

    /** @test */
    public function when_a_setter_updates_the_checklist_an_email_is_sent_to_the_moderators()
    {
        $this->markTestSkipped('Should only send when the date passed to moderator is set?');
        $this->withoutExceptionHandling();
        Mail::fake();
        $setter = create(User::class);
        $moderator1 = create(User::class);
        $moderator2 = create(User::class);
        $moderator3 = create(User::class);
        $course = create(Course::class);
        $setter->markAsSetter($course);
        $moderator1->markAsModerator($course);
        $moderator2->markAsModerator($course);

        $response = $this->actingAs($setter)->post(route('course.checklist.store', $course->id), [
            'course_id' => $course->id,
            'category' => 'main',
            'q1' => 'hello',
            'q2' => 'there',
        ]);

        Mail::assertQueued(ChecklistUpdated::class, 2);
        Mail::assertQueued(ChecklistUpdated::class, function ($mail) use ($moderator1) {
            return $mail->hasTo($moderator1);
        });
        Mail::assertQueued(ChecklistUpdated::class, function ($mail) use ($moderator2) {
            return $mail->hasTo($moderator2);
        });
    }

    /** @test */
    public function when_a_moderator_updates_the_checklist_an_email_is_sent_to_the_setters()
    {
        $this->withoutExceptionHandling();
        Mail::fake();
        $setter1 = create(User::class);
        $setter2 = create(User::class);
        $setter3 = create(User::class);
        $moderator = create(User::class);
        $course = create(Course::class);
        $setter1->markAsSetter($course);
        $setter2->markAsSetter($course);
        $moderator->markAsModerator($course);

        $this->actingAs($moderator);
        Livewire::test(LivewirePaperChecklist::class, ['course' => $course, 'category' => 'main'])
            ->call('save');

        Mail::assertQueued(ModeratorHasUpdatedTheChecklist::class, 2);
        Mail::assertQueued(ModeratorHasUpdatedTheChecklist::class, function ($mail) use ($setter1) {
            $mail->build();

            return $mail->hasTo($setter1);
        });
        Mail::assertQueued(ModeratorHasUpdatedTheChecklist::class, function ($mail) use ($setter2) {
            return $mail->hasTo($setter2);
        });
    }

    /** @test */
    public function when_an_external_updates_the_checklist_an_email_is_sent_to_the_setters()
    {
        $this->withoutExceptionHandling();
        Mail::fake();
        $setter1 = create(User::class);
        $setter2 = create(User::class);
        $setter3 = create(User::class);
        $moderator = create(User::class);
        $external = create(User::class);
        $course = create(Course::class);
        $setter1->markAsSetter($course);
        $setter2->markAsSetter($course);
        $moderator->markAsModerator($course);
        $external->markAsExternal($course);

        $this->actingAs($external);

        Livewire::test(LivewirePaperChecklist::class, ['course' => $course, 'category' => 'main'])
            ->call('save');

        Mail::assertQueued(ExternalHasUpdatedTheChecklist::class, 2);
        Mail::assertQueued(ExternalHasUpdatedTheChecklist::class, function ($mail) use ($setter1) {
            $mail->build();

            return $mail->hasTo($setter1);
        });
        Mail::assertQueued(ExternalHasUpdatedTheChecklist::class, function ($mail) use ($setter2) {
            return $mail->hasTo($setter2);
        });
    }

    /** @test */
    public function when_a_checklist_is_marked_as_ok_by_the_moderator_the_course_is_flagged_appropriately()
    {
        $this->withoutExceptionHandling();
        Mail::fake();
        $setter = create(User::class);
        $moderator = create(User::class);
        $course = create(Course::class);
        $setter->markAsSetter($course);
        $moderator->markAsModerator($course);
        $checklist = make(PaperChecklist::class);
        $course->addChecklist($checklist->fields, 'main');

        $this->assertFalse($course->isApprovedByModerator('main'));

        $checklist->fields = array_merge(
            $checklist->fields,
            [
                'overall_quality_appropriate' => true,
                'should_revise_questions' => false,
                'solution_marks_appropriate' => true,
                'solutions_marks_adjusted' => false,
            ]
        );

        $course->addChecklist($checklist->fields, 'main');

        $this->assertTrue($course->isApprovedByModerator('main'));
    }

    /** @test */
    public function when_a_checklist_is_not_marked_as_ok_by_the_moderator_the_course_is_flagged_appropriately()
    {
        $this->withoutExceptionHandling();
        Mail::fake();
        $setter = create(User::class);
        $moderator = create(User::class);
        $course = create(Course::class);
        $setter->markAsSetter($course);
        $moderator->markAsModerator($course);
        $checklist = make(PaperChecklist::class);
        $course->addChecklist($checklist->fields, 'main');

        $this->assertFalse($course->isApprovedByModerator('main'));

        $checklist->fields = array_merge(
            $checklist->fields,
            [
                'overall_quality_appropriate' => false,
                'should_revise_questions' => true,
                'solution_marks_appropriate' => false,
                'solutions_marks_adjusted' => true,
            ]
        );

        $course->addChecklist($checklist->fields, 'main');

        $this->assertFalse($course->isApprovedByModerator('main'));
    }

    /** @test */
    public function when_a_checklist_is_marked_as_ok_by_the_external_the_course_is_flagged_appropriately()
    {
        $this->withoutExceptionHandling();
        Mail::fake();
        $setter = create(User::class);
        $moderator = create(User::class);
        $external = create(User::class);
        $course = create(Course::class);
        $setter->markAsSetter($course);
        $moderator->markAsModerator($course);
        $external->markAsExternal($course);
        $checklist = make(PaperChecklist::class);
        $course->addChecklist($checklist->fields, 'main');

        $this->assertFalse($course->isApprovedByExternal('main'));

        $checklist->fields = array_merge(
            $checklist->fields,
            [
                'external_agrees_with_moderator' => true,
            ]
        );

        $course->addChecklist($checklist->fields, 'main');

        $this->assertTrue($course->isApprovedByExternal('main'));
    }

    /** @test */
    public function when_a_checklist_is_not_marked_as_ok_by_the_external_the_course_is_flagged_appropriately()
    {
        $this->withoutExceptionHandling();
        Mail::fake();
        $setter = create(User::class);
        $moderator = create(User::class);
        $external = create(User::class);
        $course = create(Course::class);
        $setter->markAsSetter($course);
        $moderator->markAsModerator($course);
        $external->markAsExternal($course);
        $checklist = make(PaperChecklist::class);
        $course->addChecklist($checklist->fields, 'main');

        $this->assertFalse($course->isApprovedByExternal('main'));

        $checklist->fields = array_merge(
            $checklist->fields,
            [
                'external_agrees_with_moderator' => false,
            ]
        );

        $course->addChecklist($checklist->fields, 'main');

        $this->assertFalse($course->isApprovedByExternal('main'));
    }
}
