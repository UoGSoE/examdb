<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\Course;
use App\Http\Livewire\PaperChecklist as LivewirePaperChecklist;
use App\Mail\ChecklistUpdated;
use App\Mail\ExternalHasUpdatedTheChecklist;
use App\Mail\ModeratorHasUpdatedTheChecklist;
use App\Mail\SetterHasUpdatedTheChecklist;
use App\Models\PaperChecklist;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class ChecklistFormTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        AcademicSession::createFirstSession();
    }

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
        $response->assertSeeLivewire('paper-checklist');
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
    public function people_associated_with_a_course_can_create_a_new_checklist()
    {
        $this->withoutExceptionHandling();
        $user = create(User::class);
        $course = create(Course::class);
        $user->markAsSetter($course);

        $this->actingAs($user);
        Livewire::test(LivewirePaperChecklist::class, ['course' => $course, 'category' => 'main'])
            ->assertDontSee('The date passed to moderator field is required')
            ->set('checklist.fields.passed_to_moderator', '')
            ->call('save', 'A')
            ->assertHasErrors(['date_passed_to_moderator'])
            ->assertSee('The date passed to moderator field is required')
            ->set('checklist.fields.passed_to_moderator', now()->format('d/m/Y'))
            ->call('save', 'A')
            ->assertHasNoErrors()
            ->assertDontSee('The date passed to moderator field is required');

        tap(PaperChecklist::firstOrFail(), function ($checklist) use ($course, $user) {
            $this->assertEquals($checklist->version, PaperChecklist::CURRENT_VERSION);
            $this->assertTrue($checklist->course->is($course));
            $this->assertTrue($checklist->user->is($user));
            $this->assertEquals('main', $checklist->category);
            $this->assertEquals(now()->format('d/m/Y'), $checklist->fields['passed_to_moderator']);
            $this->assertEquals('1', $checklist->fields['number_questions']);
            $this->assertEquals($user->full_name, $checklist->fields['question_setter_0']);
        });
    }

    /** @test */
    public function people_associated_with_a_course_can_create_a_new_checklist_with_a_specific_number_of_questions()
    {
        $this->withoutExceptionHandling();
        $user = create(User::class);
        $otherUser = create(User::class);
        $course = create(Course::class);
        $user->markAsSetter($course);
        $otherUser->markAsSetter($course);

        $this->actingAs($user);
        Livewire::test(LivewirePaperChecklist::class, ['course' => $course, 'category' => 'main'])
            ->assertDontSee('The date passed to moderator field is required')
            ->set('checklist.fields.passed_to_moderator', '')
            ->call('save', 'A')
            ->assertHasErrors(['date_passed_to_moderator'])
            ->assertSee('The date passed to moderator field is required')
            ->set('checklist.fields.passed_to_moderator', now()->format('d/m/Y'))
            ->set('checklist.fields.number_questions', 3)
            ->set('checklist.fields.question_setter_1', $otherUser->full_name)
            ->call('save', 'A')
            ->assertHasNoErrors()
            ->assertDontSee('The date passed to moderator field is required');

        tap(PaperChecklist::firstOrFail(), function ($checklist) use ($course, $user, $otherUser) {
            $this->assertEquals($checklist->version, PaperChecklist::CURRENT_VERSION);
            $this->assertTrue($checklist->course->is($course));
            $this->assertTrue($checklist->user->is($user));
            $this->assertEquals('main', $checklist->category);
            $this->assertEquals(now()->format('d/m/Y'), $checklist->fields['passed_to_moderator']);
            $this->assertEquals('3', $checklist->fields['number_questions']);
            $this->assertEquals($user->full_name, $checklist->fields['question_setter_0']);
            $this->assertEquals($otherUser->full_name, $checklist->fields['question_setter_1']);
            $this->assertEquals($user->full_name, $checklist->fields['question_setter_2']);
        });
    }

    /** @test */
    public function when_the_setter_puts_in_the_number_of_questions_on_a_paper_they_see_extra_fields_for_who_is_setting_them()
    {
        $this->withoutExceptionHandling();
        $user = create(User::class);
        $course = create(Course::class);
        $user->markAsSetter($course);

        $this->actingAs($user);
        Livewire::test(LivewirePaperChecklist::class, ['course' => $course, 'category' => 'main'])
            ->assertSee('question_setter_0') // checklists default to 1 question so there is always a first question setter field
            ->assertDontSee('question_setter_1')
            ->assertDontSee('question_setter_2')
            ->set('checklist.fields.number_questions', 3)
            ->assertSee('question_setter_0')
            ->assertSee('question_setter_1')
            ->assertSee('question_setter_2')
            ;
    }

    /** @test */
    public function various_date_fields_are_required_depending_who_the_person_is_filling_in_the_checklist()
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

        Livewire::actingAs($setter)->test(LivewirePaperChecklist::class, ['course' => $course, 'category' => 'main'])
            ->assertDontSee('The date passed to moderator field is required')
            ->set('checklist.fields.passed_to_moderator', '')
            ->call('save', 'A')
            ->assertHasErrors(['date_passed_to_moderator'])
            ->assertSee('The date passed to moderator field is required')
            ->set('checklist.fields.passed_to_moderator', now()->format('d/m/Y'))
            ->call('save', 'A')
            ->assertHasNoErrors()
            ->assertDontSee('The date passed to moderator field is required');

        Livewire::actingAs($moderator)->test(LivewirePaperChecklist::class, ['course' => $course, 'category' => 'main'])
            ->assertDontSee('The date passed to moderator field is required')
            ->set('checklist.fields.passed_to_moderator', '')
            ->call('save', 'A')
            ->assertHasErrors(['date_passed_to_moderator'])
            ->assertSee('The date passed to moderator field is required')
            ->set('checklist.fields.passed_to_moderator', now()->format('d/m/Y'))
            ->call('save', 'A')
            ->assertHasNoErrors()
            ->assertDontSee('The date passed to moderator field is required');
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
            'fields' => [
                'thing' => 'whatever',
                'colour' => 'left',
            ],
        ]);

        $response = $this->actingAs($user)->get(route('course.checklist.create', [
            'course' => $course->id,
            'category' => 'main',
        ]));

        $response->assertOk();
        $response->assertViewHas('checklist');
        $this->assertEquals($course->id, $response->data('checklist')->course_id);
        $this->assertNull($response->data('checklist')->id);
        $this->assertEquals($existingChecklist->category, $response->data('checklist')->category);
        $this->assertEquals($existingChecklist->version, $response->data('checklist')->version);
        $this->assertEquals($existingChecklist->fields, $response->data('checklist')->fields);
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
        $this->markTestSkipped('TODO fix up after removing wkhtmltopdf');
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
    public function when_a_setter_updates_the_checklist_passed_to_moderator_date_an_email_is_sent_to_the_moderators()
    {
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
        option(['glasgow_internal_moderation_deadline' => now()->format('Y-m-d')]);

        $this->actingAs($setter);
        Livewire::test(LivewirePaperChecklist::class, ['course' => $course, 'category' => 'main'])
            ->set('checklist.fields.passed_to_moderator', now()->format('d/m/Y'))
            ->call('save', 'A');

        Mail::assertQueued(SetterHasUpdatedTheChecklist::class, 2);
        Mail::assertQueued(SetterHasUpdatedTheChecklist::class, function ($mail) use ($moderator1) {
            return $mail->hasTo($moderator1) && $mail->deadline == now()->format('d/m/Y');
        });
        Mail::assertQueued(SetterHasUpdatedTheChecklist::class, function ($mail) use ($moderator2) {
            return $mail->hasTo($moderator2);
        });
    }

    /** @test */
    public function if_a_setter_who_is_also_a_moderator_updates_the_checklist_passed_to_moderator_an_email_is_sent_to_the_moderators_except_the_setter()
    {
        $this->withoutExceptionHandling();
        Mail::fake();
        $setter = create(User::class);
        $moderator1 = create(User::class);
        $moderator2 = create(User::class);
        $moderator3 = create(User::class);
        $course = create(Course::class);
        $setter->markAsSetter($course);
        $setter->markAsModerator($course);
        $moderator1->markAsModerator($course);
        $moderator2->markAsModerator($course);
        option(['glasgow_internal_moderation_deadline' => now()->format('Y-m-d')]);

        $this->actingAs($setter);
        Livewire::test(LivewirePaperChecklist::class, ['course' => $course, 'category' => 'main'])
            ->set('checklist.fields.passed_to_moderator', now()->format('d/m/Y'))
            ->call('save', 'A');

        Mail::assertQueued(SetterHasUpdatedTheChecklist::class, 2);
        Mail::assertQueued(SetterHasUpdatedTheChecklist::class, function ($mail) use ($moderator1) {
            return $mail->hasTo($moderator1) && $mail->deadline == now()->format('d/m/Y');
        });
        Mail::assertQueued(SetterHasUpdatedTheChecklist::class, function ($mail) use ($moderator2) {
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
            ->set('checklist.fields.overall_quality_appropriate', '1')
            ->call('save', 'B');

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
    public function when_a_moderator_updates_the_checklist_and_says_its_not_acceptable_they_must_provide_a_reason()
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
            ->set('checklist.fields.overall_quality_appropriate', '0')
            ->call('save', 'B')
            ->assertHasErrors(['comments' => 'required'])
            ->assertSee('The comments field is required.');


        Mail::assertNothingQueued();

        Livewire::test(LivewirePaperChecklist::class, ['course' => $course, 'category' => 'main'])
            ->set('checklist.fields.solution_marks_appropriate', '0')
            ->call('save', 'C')
            ->assertHasErrors(['solution_comments' => 'required'])
            ->assertSee('The solution comments field is required.');


        Mail::assertNothingQueued();
    }

    /** @test */
    public function when_a_moderator_who_is_also_a_setter_updates_the_checklist_an_email_is_sent_to_the_setters_except_the_moderator()
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
        $moderator->markAsSetter($course);

        $this->actingAs($moderator);
        Livewire::test(LivewirePaperChecklist::class, ['course' => $course, 'category' => 'main'])
            ->set('checklist.fields.overall_quality_appropriate', '1')
            ->call('save', 'B');

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
            ->call('save', 'D');

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
        login($moderator);
        $checklist = make(PaperChecklist::class);
        $course->addChecklist($checklist->fields, 'main', 'B');

        $this->assertFalse($course->isApprovedByModerator('main'));

        $checklist->fields = array_merge(
            $checklist->fields,
            [
                'overall_quality_appropriate' => "1",
                'should_revise_questions' => "0",
                'solution_marks_appropriate' => "1",
                'solutions_marks_adjusted' => "0",
            ]
        );

        $course->addChecklist($checklist->fields, 'main', 'B');
        $course->addChecklist($checklist->fields, 'main', 'C');

        $this->assertTrue($course->fresh()->isApprovedByModerator('main'));
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
        login($moderator);
        $checklist = $course->getNewChecklist('main');
        $course->addChecklist($checklist->fields, 'main', 'B');
        $this->assertFalse($course->isApprovedByModerator('main'));

        $checklist = $course->getNewChecklist('main');
        $checklist->fields = array_merge(
            $checklist->fields,
            [
                'overall_quality_appropriate' => "1",
                'should_revise_questions' => "1",
            ]
        );

        $course->fresh()->addChecklist($checklist->fields, 'main', 'B');

        $fields = $checklist->fields;
        $fields['solution_marks_appropriate'] = "0";
        $fields['solutions_marks_adjusted'] = "1";

        $course->addChecklist($checklist->fields, 'main', 'B');

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
        login($external);
        $checklist = make(PaperChecklist::class);
        $course->addChecklist($checklist->fields, 'main', 'A');

        $this->assertFalse($course->isApprovedByExternal('main'));

        $checklist->fields = array_merge(
            $checklist->fields,
            [
                'external_agrees_with_moderator' => true,
            ]
        );

        $course->addChecklist($checklist->fields, 'main', 'D');

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
        $course->addChecklist($checklist->fields, 'main', 'A');

        $this->assertFalse($course->isApprovedByExternal('main'));

        $checklist->fields = array_merge(
            $checklist->fields,
            [
                'external_agrees_with_moderator' => false,
            ]
        );

        $course->addChecklist($checklist->fields, 'main', 'D');

        $this->assertFalse($course->isApprovedByExternal('main'));
    }

    /** @test */
    public function we_can_make_an_assessment_checklist()
    {
        $this->withoutExceptionHandling();
        $user = create(User::class);
        $course = create(Course::class);
        $user->markAsSetter($course);

        $this->actingAs($user);
        Livewire::test(LivewirePaperChecklist::class, ['course' => $course, 'category' => 'assessment'])
            ->set('checklist.fields.passed_to_moderator', now()->format('d/m/Y'))
            ->call('save', 'A')
            ->assertHasNoErrors();

        tap(PaperChecklist::first(), function ($checklist) use ($course, $user) {
            $this->assertEquals($checklist->version, PaperChecklist::CURRENT_VERSION);
            $this->assertTrue($checklist->course->is($course));
            $this->assertTrue($checklist->user->is($user));
            $this->assertEquals('assessment', $checklist->category);
        });
    }

    /** @test */
    public function when_a_checklist_is_submitted_only_the_fields_appropriate_to_the_users_role_are_changed()
    {
        $this->withoutExceptionHandling();
        $setter = create(User::class);
        $moderator = create(User::class);
        $external = create(User::class);
        $course = create(Course::class);
        $setter->markAsSetter($course);
        $moderator->markAsModerator($course);
        $external->markAsExternal($course);
        $checklist = create(PaperChecklist::class, [
            'course_id' => $course->id,
            'category' => 'main',
            'fields' => [
                'course_code' => 'ENG2001', // a setter-only field
                'course_title' => 'Original Title', // a setter-only field
                'moderator_comments' => 'Blah de blah', // moderator-only field
                'external_comments' => 'Tum te tum', // external-only field
            ],
        ]);

        $this->actingAs($setter);
        Livewire::test(LivewirePaperChecklist::class, ['course' => $course, 'category' => 'main'])
            ->set('checklist.fields.course_title', 'New course title')
            ->set('checklist.fields.passed_to_moderator', now()->format('d/m/Y'))
            ->set('checklist.fields.moderator_comments', 'Mwah-ha-haaaa')
            ->set('checklist.fields.external_comments', 'The setter is pure amazing btw')
            ->call('save', 'A')
            ->assertHasNoErrors();

        tap(PaperChecklist::all()->last(), function ($checklist) {
            // this should be unchanged as we didn't ->set it in livewire
            $this->assertEquals('ENG2001', $checklist->fields['course_code']);
            // this should be changed as we did ->set it in livewire
            $this->assertEquals('New course title', $checklist->fields['course_title']);
            // this should be changed as we did ->set it in livewire
            $this->assertEquals(now()->format('d/m/Y'), $checklist->fields['passed_to_moderator']);
            // this should be unchanged as although we set it, we are not the moderator
            $this->assertEquals('Blah de blah', $checklist->fields['moderator_comments']);
            // this should be unchanged as although we set it, we are not the external
            $this->assertEquals('Tum te tum', $checklist->fields['external_comments']);
        });

        $this->actingAs($moderator);
        Livewire::test(LivewirePaperChecklist::class, ['course' => $course, 'category' => 'main'])
            ->set('checklist.fields.course_title', 'Some other title')
            ->set('checklist.fields.overall_quality_appropriate', "1")
            ->set('checklist.fields.moderator_comments', 'Cool story, bro')
            ->set('checklist.fields.external_comments', 'The external totes agrees with me')
            ->call('save', 'B')
            ->assertHasNoErrors();

        tap(PaperChecklist::all()->last(), function ($checklist) {
            // this should be unchanged as we didn't ->set it in livewire
            $this->assertEquals('ENG2001', $checklist->fields['course_code']);
            // this shouldn't be changed as although we set it in livewire, we are not the setter
            $this->assertEquals('New course title', $checklist->fields['course_title']);
            // this should be changed as we set it and are the moderator
            $this->assertEquals('Cool story, bro', $checklist->fields['moderator_comments']);
            // this should be unchanged as although we set it, we are not the external
            $this->assertEquals('Tum te tum', $checklist->fields['external_comments']);
        });

        $this->actingAs($external);
        Livewire::test(LivewirePaperChecklist::class, ['course' => $course, 'category' => 'main'])
            ->set('checklist.fields.course_title', 'Some other title')
            ->set('checklist.fields.moderator_comments', 'Cool story, bro')
            ->set('checklist.fields.external_comments', 'Dont like the font')
            ->call('save', 'D')
            ->assertHasNoErrors();

        tap(PaperChecklist::all()->last(), function ($checklist) {
            // this should be unchanged as we didn't ->set it in livewire
            $this->assertEquals('ENG2001', $checklist->fields['course_code']);
            // this shouldn't be changed as although we set it in livewire, we are not the setter
            $this->assertEquals('New course title', $checklist->fields['course_title']);
            // this shouldn't be changed as although we set it in livewire, we are not the moderator
            $this->assertEquals('Cool story, bro', $checklist->fields['moderator_comments']);
            // this should be changed as we set it and we are the external
            $this->assertEquals('Dont like the font', $checklist->fields['external_comments']);
        });

        // as we cache some of the db queries for five seconds as part of this process, flush the cache
        // so the tests don't break
        Cache::flush();

        // now we test for someone who is a setter and moderator on the same course
        $moderator->markAsSetter($course);
        $this->actingAs($moderator);
        Livewire::test(LivewirePaperChecklist::class, ['course' => $course, 'category' => 'main'])
            ->set('checklist.fields.course_title', 'Whatevs')
            ->set('checklist.fields.overall_quality_appropriate', '1')
            ->set('checklist.fields.moderator_comments', 'Spanners!')
            ->set('checklist.fields.external_comments', 'Brrr')
            ->call('save', 'B')
            ->assertHasNoErrors();

        tap(PaperChecklist::all()->last(), function ($checklist) {
            // this should be unchanged as we didn't ->set it in livewire
            $this->assertEquals('ENG2001', $checklist->fields['course_code']);
            // this should be changed as we set it in livewire and we are a setter
            $this->assertEquals('New course title', $checklist->fields['course_title']);
            // this should be changed as we set it in livewire and we are also a moderator
            $this->assertEquals('Spanners!', $checklist->fields['moderator_comments']);
            // this should be unchanged as although we set it, we are not the external
            $this->assertEquals('Dont like the font', $checklist->fields['external_comments']);
        });
    }
}
