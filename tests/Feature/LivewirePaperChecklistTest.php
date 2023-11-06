<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\Course;
use App\Models\Paper;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LivewirePaperChecklistTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        AcademicSession::createFirstSession();
    }

    /** @test */
    public function we_can_see_the_paper_checklist_page_for_a_course()
    {
        $this->withoutExceptionHandling();
        $course = create(Course::class);
        $user = create(User::class);
        $user->markAsSetter($course);
        $paper = create(Paper::class, ['course_id' => $course->id, 'category' => 'main']);

        $response = $this->actingAs($user)->get(route('course.checklist.create', [
            'course' => $course->id,
            'category' => 'main',
        ]));

        $response->assertOk();
        $response->assertViewHas('course', $course);
        $response->assertSeeLivewire('paper-checklist');
    }

    /** @test */
    public function the_list_of_setters_is_the_course_setters_followed_by_all_other_staff()
    {
        $setter1 = User::factory()->create(['surname' => 'aaaa']);
        $setter2 = User::factory()->create(['surname' => 'bbbb']);
        $setter3 = User::factory()->create(['surname' => 'cccc']);
        $otherStaff1 = User::factory()->create(['surname' => 'dddd']);
        $otherStaff2 = User::factory()->create(['surname' => 'eeee']);
        $course = create(Course::class);
        $course->setters()->attach([$setter1->id, $setter2->id, $setter3->id]);
        $paper = create(Paper::class, ['course_id' => $course->id, 'category' => 'main', 'user_id' => $setter1->id]);

        Livewire::actingAs($setter1)->test('paper-checklist', ['course' => $course])
            ->assertSet('setters.0.id', $setter1->id)
            ->assertSet('setters.1.id', $setter2->id)
            ->assertSet('setters.2.id', $setter3->id)
            ->assertSet('setters.3.id', $otherStaff1->id)
            ->assertSet('setters.4.id', $otherStaff2->id);
    }

    /** @test */
    public function when_the_number_of_questions_is_updated_the_correct_dynamic_fields_are_created()
    {
        $this->withoutExceptionHandling();
        $course = create(Course::class);
        $user = create(User::class);
        $user->markAsSetter($course);
        $paper = create(Paper::class, ['course_id' => $course->id, 'category' => 'main']);

        Livewire::actingAs($user)->test('paper-checklist', ['course' => $course])
            ->assertSet('checklist.fields.question_setter_0', $user->full_name)
            ->assertNotSet('checklist.fields.question_setter_1', $user->full_name)
            ->assertSet('checklist.fields.question_datasheet_0', '')
            ->assertNotSet('checklist.fields.question_datasheet_1', '', strict: true)
            ->set('checklist.fields.number_questions', 2)
            ->assertSet('checklist.fields.question_setter_0', $user->full_name)
            ->assertSet('checklist.fields.question_setter_1', $user->full_name)
            ->assertSet('checklist.fields.question_datasheet_0', '')
            ->assertSet('checklist.fields.question_datasheet_1', '', strict: true);
    }

    /** @test */
    public function when_section_A_is_saved_the_dynamic_fields_are_correctly_stored()
    {
        $this->withoutExceptionHandling();
        $course = create(Course::class);
        $user = create(User::class);
        $user->markAsSetter($course);
        $paper = create(Paper::class, ['course_id' => $course->id, 'category' => 'main']);

        Livewire::actingAs($user)->test('paper-checklist', ['course' => $course])
            ->assertSet('checklist.fields.question_setter_0', $user->full_name)
            ->assertNotSet('checklist.fields.question_setter_1', $user->full_name)
            ->assertSet('checklist.fields.question_datasheet_0', '')
            ->assertNotSet('checklist.fields.question_datasheet_1', '', strict: true)
            ->set('checklist.fields.number_questions', 2)
            ->assertSet('checklist.fields.question_setter_0', $user->full_name)
            ->assertSet('checklist.fields.question_setter_1', $user->full_name)
            ->assertSet('checklist.fields.question_datasheet_0', '')
            ->assertSet('checklist.fields.question_datasheet_1', '', strict: true)
            ->set('checklist.fields.question_datasheet_0', 'yes')
            ->set('checklist.fields.question_datasheet_1', 'no')
            ->set('checklist.fields.passed_to_moderator', now()->format('d/m/Y'))
            ->call('save', 'A')
            ->assertHasNoErrors();

        tap($paper->course->checklists->first(), function ($checklist) use ($user) {
            $this->assertEquals($user->full_name, $checklist->fields['question_setter_0']);
            $this->assertEquals($user->full_name, $checklist->fields['question_setter_1']);
            $this->assertEquals('yes', $checklist->fields['question_datasheet_0']);
            $this->assertEquals('no', $checklist->fields['question_datasheet_1']);
        });
    }
}
