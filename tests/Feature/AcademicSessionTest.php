<?php

namespace Tests\Feature;

use App\User;
use Carbon\Carbon;
use Tests\TestCase;
use App\AcademicSession;
use App\Course;
use App\Discipline;
use Illuminate\Support\Facades\Queue;
use App\Jobs\CopyDataToNewAcademicSession;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AcademicSessionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function logged_in_users_default_to_the_latest_academic_session()
    {
        $oldSession = AcademicSession::factory()->create(['session' => '1904/1905', 'created_at' => '2018-01-01']);
        $newSession = AcademicSession::factory()->create(['session' => '2020/2021', 'created_at' => '2020-01-01']);
        $midSession = AcademicSession::factory()->create(['session' => '1944/1945', 'created_at' => '2019-01-01']);
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/home');

        $response->assertOk();
        $response->assertSessionHas('academic_session', '2020/2021');
    }

    /** @test */
    public function admin_users_who_have_chosen_a_specific_academic_session_dont_have_it_replaced_by_the_default()
    {
        $oldSession = AcademicSession::factory()->create(['session' => '1904/1905', 'created_at' => '2018-01-01']);
        $newSession = AcademicSession::factory()->create(['session' => '2020/2021', 'created_at' => '2020-01-01']);
        $midSession = AcademicSession::factory()->create(['session' => '1944/1945', 'created_at' => '2019-01-01']);
        $user = User::factory()->create();

        $response = $this->actingAs($user)->withSession(['academic_session' => '1904/1905'])->get('/home');

        $response->assertOk();
        $response->assertSessionHas('academic_session', '1904/1905');
    }

    /** @test */
    public function if_there_are_no_existing_academic_sessions_then_one_is_created()
    {
        $user = User::factory()->create();

        $this->travel(Carbon::createFromFormat('Y-m-d', '2020-01-01'));

        $response = $this->actingAs($user)->get('/home');

        $response->assertOk();
        $newSession = AcademicSession::firstOrFail();
        $this->assertEquals('2020/2021', $newSession->session);
        $response->assertSessionHas('academic_session', '2020/2021');

        $this->travelBack();
    }

    /** @test */
    public function if_it_is_after_august_and_a_new_academic_session_is_automatically_created_then_it_is_set_to_the_next_year()
    {
        $user = User::factory()->create();

        $this->travel(Carbon::createFromFormat('Y-m-d', '2020-09-01'));

        $response = $this->actingAs($user)->get('/home');

        $response->assertOk();
        $newSession = AcademicSession::firstOrFail();
        $this->assertEquals('2021/2022', $newSession->session);
        $response->assertSessionHas('academic_session', '2021/2022');

        $this->travelBack();
    }

    /** @test */
    public function admin_users_can_change_their_academic_session()
    {
        $admin = User::factory()->admin()->create();
        $session1 = AcademicSession::factory()->create(['session' => '1980/1981']);
        $session2 = AcademicSession::factory()->create(['session' => '1990/1991']);

        $response = $this->actingAs($admin)->post(route('academicsession.set', ['session' => $session2->id]));

        $response->assertRedirect('/home');
        $response->assertSessionHas('academic_session' , '1990/1991');

        $response = $this->actingAs($admin)->post(route('academicsession.set', ['session' => $session1->id]));

        $response->assertRedirect('/home');
        $response->assertSessionHas('academic_session' , '1980/1981');
    }

    /** @test */
    public function regular_users_cant_change_their_academic_session()
    {
        $user = User::factory()->create();
        $session1 = AcademicSession::factory()->create(['session' => '1990/1991', 'created_at' => '2019-01-01']);
        $session2 = AcademicSession::factory()->create(['session' => '1980/1981', 'created_at' => '2018-01-01']);

        $response = $this->actingAs($user)->post(route('academicsession.set', ['session' => $session2->id]));

        $response->assertStatus(403);
        $response->assertSessionHas('academic_session' , '1990/1991'); // it defaults to the latest created_at one
    }

    /** @test */
    public function admins_can_create_a_new_academic_session()
    {
        Queue::fake();
        $admin = User::factory()->admin()->create();
        $existingSession = AcademicSession::factory()->create(['session' => '1980/1981']);

        $response = $this->actingAs($admin)->post(route('academicsession.store'), [
            'session' => '1990/1991',
        ]);

        $response->assertRedirect('/home');
        $this->assertDatabaseHas('academic_sessions', ['session' => '1990/1991']);
        Queue::assertPushed(CopyDataToNewAcademicSession::class, 1);
        Queue::assertPushed(CopyDataToNewAcademicSession::class, fn ($job) => $job->session->session === '1990/1991');
    }

    /** @test */
    public function when_the_new_session_is_created_the_existing_session_data_is_copied_and_updated_with_the_right_session_info()
    {
        $oldSession = AcademicSession::factory()->create(['session' => '1980/1981']);
        $discipline1 = Discipline::factory()->create(['academic_session_id' => $oldSession->id]);
        $discipline2 = Discipline::factory()->create(['academic_session_id' => $oldSession->id]);
        $course1 = Course::factory()->create(['academic_session_id' => $oldSession->id, 'discipline_id' => $discipline1->id]);
        $course2 = Course::factory()->create(['academic_session_id' => $oldSession->id, 'discipline_id' => $discipline2->id]);
        $user1 = User::factory()->create(['academic_session_id' => $oldSession->id]);
        $user2 = User::factory()->create(['academic_session_id' => $oldSession->id]);
        $newSession = AcademicSession::factory()->create(['session' => '1990/1991']);

        CopyDataToNewAcademicSession::dispatchSync($newSession);

        $this->assertDatabaseHas('courses', ['code' => $course1->code, 'academic_session_id' => $newSession->id]);
        $this->assertDatabaseHas('courses', ['code' => $course2->code, 'academic_session_id' => $newSession->id]);
        $this->assertDatabaseHas('disciplines', ['title' => $discipline1->title, 'academic_session_id' => $newSession->id]);
        $this->assertDatabaseHas('disciplines', ['title' => $discipline2->title, 'academic_session_id' => $newSession->id]);
        $this->assertDatabaseHas('users', ['username' => $user1->username, 'academic_session_id' => $newSession->id]);
        $this->assertDatabaseHas('users', ['username' => $user2->username, 'academic_session_id' => $newSession->id]);

        $newCourse1 = Course::where('code', '=', $course1->code)->where('academic_session_id', '=', $newSession->id)->firstOrFail();
        $newDiscpline1 = Discipline::where('title', '=', $discipline1->title)->where('academic_session_id', '=', $newSession->id)->firstOrFail();
        $this->assertTrue($newCourse1->discipline->is($newDiscpline1));
        $newCourse2 = Course::where('code', '=', $course2->code)->where('academic_session_id', '=', $newSession->id)->firstOrFail();
        $newDiscpline2 = Discipline::where('title', '=', $discipline2->title)->where('academic_session_id', '=', $newSession->id)->firstOrFail();
        $this->assertTrue($newCourse2->discipline->is($newDiscpline2));
    }

}
