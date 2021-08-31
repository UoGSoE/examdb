<?php

namespace Tests\Feature;

use App\User;
use App\Course;
use Carbon\Carbon;
use App\Discipline;
use Tests\TestCase;
use App\AcademicSession;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use App\Mail\DataWasCopiedToNewSession;
use App\Jobs\CopyDataToNewAcademicSession;
use App\Scopes\CurrentAcademicSessionScope;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

class AcademicSessionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function logged_in_users_default_to_the_default_academic_session()
    {
        $oldSession = AcademicSession::factory()->create(['session' => '1904/1905', 'is_default' => false]);
        $newSession = AcademicSession::factory()->create(['session' => '2020/2021', 'is_default' => true]);
        $midSession = AcademicSession::factory()->create(['session' => '1944/1945', 'is_default' => false]);
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/home');

        $response->assertOk();
        $response->assertSessionHas('academic_session', '2020/2021');
    }

    /** @test */
    public function when_users_login_their_records_are_fetched_from_the_default_session()
    {
        $this->withoutExceptionHandling();
        $oldSession = AcademicSession::factory()->create(['session' => '1904/1905', 'is_default' => false]);
        $newSession = AcademicSession::factory()->create(['session' => '2020/2021', 'is_default' => true]);
        $midSession = AcademicSession::factory()->create(['session' => '1944/1945', 'is_default' => false]);
        $oldUser = User::factory()->create(['username' => 'fred', 'surname' => 'smith', 'password' => bcrypt('hello'), 'academic_session_id' => $oldSession->id]);
        $newUser = User::factory()->create(['username' => 'fred', 'surname' => 'nee smith', 'password' => bcrypt('hello'), 'academic_session_id' => $newSession->id]);

        $response = $this->post('/login', [
            'username' => 'fred',
            'password' => 'hello',
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('academic_session', '2020/2021');
        $this->assertEquals('nee smith', auth()->user()->surname);
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
        $user = User::factory()->create(['academic_session_id' => null]); // stop the factory creating a new academic session

        $this->travelTo(Carbon::createFromFormat('Y-m-d', '2020-01-01'));

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
        AcademicSession::createFirstSession();
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
        $this->withoutExceptionHandling();
        $defaultSession = AcademicSession::createFirstSession();
        $session1 = AcademicSession::factory()->create(['session' => '1980/1981']);
        $session2 = AcademicSession::factory()->create(['session' => '1990/1991']);
        $adminV1 = User::factory()->admin()->create(['username' => 'jenny', 'academic_session_id' => $defaultSession->id]);
        $adminV2 = User::factory()->admin()->create(['username' => 'jenny', 'academic_session_id' => $session2->id]);

        login($adminV1);
        session(['academic_session' => $defaultSession->session]);
        $response = $this->post(route('academicsession.set', ['session' => $session2->id]));

        $response->assertRedirect('/home');
        $response->assertSessionHas('academic_session' , '1990/1991');
        $this->assertTrue(auth()->user()->is($adminV2));

        login($adminV2);
        session(['academic_session' => $session2->session]);
        $response = $this->post(route('academicsession.set', ['session' => $defaultSession->id]));

        $response->assertRedirect('/home');
        $response->assertSessionHas('academic_session' , $defaultSession->session);
        $this->assertTrue(auth()->user()->is($adminV1));
    }

    /** @test */
    public function regular_users_cant_change_their_academic_session()
    {
        $user = User::factory()->create();
        $session1 = AcademicSession::factory()->create(['session' => '1990/1991', 'is_default' => true]);
        $session2 = AcademicSession::factory()->create(['session' => '1980/1981', 'is_default' => false]);

        $response = $this->actingAs($user)->post(route('academicsession.set', ['session' => $session2->id]));

        $response->assertStatus(403);
        $response->assertSessionHas('academic_session' , '1990/1991'); // it defaults to the latest created_at one
    }

    /** @test */
    public function regular_users_cant_see_the_manage_sessions_form()
    {
        AcademicSession::createFirstSession();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('academicsession.edit'));

        $response->assertForbidden();
    }

    /** @test */
    public function admins_can_see_the_manage_sessions_form()
    {
        AcademicSession::createFirstSession();
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('academicsession.edit'));

        $response->assertOk();
        $response->assertSee('Manage Academic Sessions');
    }

    /** @test */
    public function admins_can_create_a_new_academic_session()
    {
        $this->withoutExceptionHandling();
        Queue::fake();
        $admin = User::factory()->admin()->create();
        $existingSession = AcademicSession::factory()->create(['session' => '1980/1981', 'is_default' => true]);

        $response = $this->actingAs($admin)->post(route('academicsession.store'), [
            'new_session_year_1' => '2011',
            'new_session_year_2' => '2012',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/home');
        $this->assertDatabaseHas('academic_sessions', ['session' => '2011/2012']);
        Queue::assertPushed(CopyDataToNewAcademicSession::class, 1);
        Queue::assertPushed(CopyDataToNewAcademicSession::class, fn ($job) => $job->targetSession->session === '2011/2012');
    }

    /** @test */
    public function when_a_new_session_is_created_the_cached_navbar_academic_sessions_are_flushed()
    {
        $this->withoutExceptionHandling();
        Queue::fake();
        $admin = User::factory()->admin()->create();
        $existingSession = AcademicSession::factory()->create(['session' => '1980/1981', 'is_default' => true]);

        Cache::shouldReceive('forget')->once()->with('navbarAcademicSessions');

        $response = $this->actingAs($admin)->post(route('academicsession.store'), [
            'new_session_year_1' => '2011',
            'new_session_year_2' => '2012',
        ]);
    }

    /** @test */
    public function when_the_new_session_is_created_the_existing_session_data_is_copied_and_updated_with_the_right_session_info()
    {
        $admin = User::factory()->admin()->create();
        $oldSession = AcademicSession::factory()->create(['session' => '1980/1981', 'is_default' => true]);
        $discipline1 = Discipline::factory()->create(['academic_session_id' => $oldSession->id]);
        $discipline2 = Discipline::factory()->create(['academic_session_id' => $oldSession->id]);
        $course1 = Course::factory()->create(['academic_session_id' => $oldSession->id, 'discipline_id' => $discipline1->id]);
        $course2 = Course::factory()->create(['academic_session_id' => $oldSession->id, 'discipline_id' => $discipline2->id]);
        $softDeletedCourse = Course::factory()->create(['academic_session_id' => $oldSession->id, 'discipline_id' => $discipline2->id]);
        $softDeletedCourse->delete();
        $user1 = User::factory()->create(['academic_session_id' => $oldSession->id]);
        $user2 = User::factory()->create(['academic_session_id' => $oldSession->id]);
        $softDeletedUser = User::factory()->create(['academic_session_id' => $oldSession->id]);
        $softDeletedUser->delete();
        $externaUser = User::factory()->external()->create(['academic_session_id' => $oldSession->id]);
        $user1->markAsSetter($course1);
        $user2->markAsModerator($course1);
        $user1->markAsModerator($course2);
        $user2->markAsModerator($course2);
        $externaUser->markAsExternal($course2);
        $newSession = AcademicSession::factory()->create(['session' => '1990/1991']);

        CopyDataToNewAcademicSession::dispatchSync($oldSession, $newSession, $admin);

        $this->assertDatabaseHas('courses', ['code' => $course1->code, 'academic_session_id' => $newSession->id]);
        $this->assertDatabaseHas('courses', ['code' => $course2->code, 'academic_session_id' => $newSession->id]);
        $this->assertDatabaseHas('courses', ['code' => $softDeletedCourse->code, 'academic_session_id' => $newSession->id]);
        $this->assertDatabaseHas('disciplines', ['title' => $discipline1->title, 'academic_session_id' => $newSession->id]);
        $this->assertDatabaseHas('disciplines', ['title' => $discipline2->title, 'academic_session_id' => $newSession->id]);
        $this->assertDatabaseHas('users', ['username' => $user1->username, 'academic_session_id' => $newSession->id]);
        $this->assertDatabaseHas('users', ['username' => $user2->username, 'academic_session_id' => $newSession->id]);
        $this->assertDatabaseHas('users', ['username' => $softDeletedUser->username, 'academic_session_id' => $newSession->id]);
        $this->assertDatabaseHas('users', ['username' => $externaUser->username, 'academic_session_id' => $newSession->id]);

        // keep the global academic session scope happy when querying relations
        login($admin);
        session(['academic_session' => $newSession->session]);

        $newCourse1 = Course::withoutGlobalScope(CurrentAcademicSessionScope::class)->where('code', '=', $course1->code)->where('academic_session_id', '=', $newSession->id)->firstOrFail();
        $newDiscpline1 = Discipline::withoutGlobalScope(CurrentAcademicSessionScope::class)->where('title', '=', $discipline1->title)->where('academic_session_id', '=', $newSession->id)->firstOrFail();
        $this->assertTrue($newCourse1->discipline->is($newDiscpline1));
        $this->assertCount(1, $newCourse1->moderators);
        $this->assertCount(1, $newCourse1->setters);
        $this->assertCount(0, $newCourse1->externals);
        $this->assertEquals($newCourse1->setters->first()->username, $user1->username);
        $this->assertEquals($newCourse1->moderators->first()->username, $user2->username);

        $newCourse2 = Course::where('code', '=', $course2->code)->where('academic_session_id', '=', $newSession->id)->firstOrFail();
        $newDiscpline2 = Discipline::where('title', '=', $discipline2->title)->where('academic_session_id', '=', $newSession->id)->firstOrFail();
        $this->assertTrue($newCourse2->discipline->is($newDiscpline2));
        $this->assertCount(0, $newCourse2->setters);
        $this->assertCount(2, $newCourse2->moderators);
        $this->assertCount(1, $newCourse2->externals);
        $this->assertEquals(1, $newCourse2->moderators->where('username', $user1->username)->count());
        $this->assertEquals(1, $newCourse2->moderators->where('username', $user2->username)->count());
        $this->assertEquals($newCourse2->externals->first()->username, $externaUser->username);
    }

    /** @test */
    public function when_course_data_is_copied_to_a_new_session_all_of_the_flags_on_it_are_cleared()
    {
        Mail::fake();
        $admin = User::factory()->admin()->create();
        $oldSession = AcademicSession::factory()->create(['session' => '1980/1981', 'is_default' => true]);
        $newSession = AcademicSession::factory()->create(['session' => '1990/1991', 'is_default' => false]);
        $course1 = Course::factory()->create(['academic_session_id' => $oldSession->id]);
        foreach ($course1->flagsToClearOnDuplication as $flag) {
            $course1->$flag = true;
        }
        $course1->save();

        CopyDataToNewAcademicSession::dispatchSync($oldSession, $newSession, $admin);

        $copyOfCourse = Course::withoutGlobalScope(CurrentAcademicSessionScope::class)
                ->where('code', '=', $course1->code)
                ->where('academic_session_id', '=', $newSession->id)
                ->firstOrFail();
        foreach ($copyOfCourse->flagsToClearOnDuplication as $flag) {
            $this->assertFalse($copyOfCourse->$flag);
        }
    }

    /** @test */
    public function once_the_data_is_copied_an_email_is_sent_to_the_user_who_requested_the_new_session()
    {
        Mail::fake();
        $admin = User::factory()->admin()->create();
        $oldSession = AcademicSession::factory()->create(['session' => '1980/1981', 'is_default' => true]);
        $newSession = AcademicSession::factory()->create(['session' => '1990/1991', 'is_default' => false]);

        CopyDataToNewAcademicSession::dispatchSync($oldSession, $newSession, $admin);

        Mail::assertQueued(DataWasCopiedToNewSession::class, 1);
        Mail::assertQueued(DataWasCopiedToNewSession::class, function ($mail) use ($admin, $newSession) {
            return $mail->hasTo($admin->email) && $mail->session->is($newSession);
        });
    }

    /** @test */
    public function regular_users_cant_change_the_default_session()
    {
        $user = User::factory()->create();
        $session1 = AcademicSession::factory()->create(['session' => '1980/1981', 'is_default' => true]);
        $session2 = AcademicSession::factory()->create(['session' => '1981/1982', 'is_default' => false]);

        $response = $this->actingAs($user)->post(route('academicsession.default.update', $session2->id));

        $response->assertForbidden();
        $this->assertTrue($session1->fresh()->is_default);
        $this->assertFalse($session2->fresh()->is_default);
    }

    /** @test */
    public function admins_can_change_the_default_session()
    {
        $admin = User::factory()->admin()->create();
        $session1 = AcademicSession::factory()->create(['session' => '1980/1981', 'is_default' => true]);
        $session2 = AcademicSession::factory()->create(['session' => '1981/1982', 'is_default' => false]);

        $response = $this->actingAs($admin)->from(route('academicsession.edit'))->post(route('academicsession.default.update', $session2->id));

        $response->assertRedirect(route('academicsession.edit'));
        $this->assertFalse($session1->fresh()->is_default);
        $this->assertTrue($session2->fresh()->is_default);
    }

    /** @test */
    public function there_is_an_artisan_command_to_retrofit_academic_sessions_into_the_old_engineering_only_database()
    {
        AcademicSession::createFirstSession();
        $session = AcademicSession::first();
        $course1 = Course::factory()->create(['academic_session_id' => null]);
        $course2 = Course::factory()->create(['academic_session_id' => null]);
        $user1 = User::factory()->create(['academic_session_id' => null]);
        $discipline1 = Discipline::factory()->create(['academic_session_id' => null]);
        $discipline2 = Discipline::factory()->create(['academic_session_id' => null]);

        $this->artisan('examdb:retrofit-academic-session ' . $session->session);

        $this->assertEquals($session->id, $course1->fresh()->academic_session_id);
        $this->assertEquals($session->id, $course2->fresh()->academic_session_id);
        $this->assertEquals($session->id, $user1->fresh()->academic_session_id);
        $this->assertEquals($session->id, $discipline1->fresh()->academic_session_id);
        $this->assertEquals($session->id, $discipline2->fresh()->academic_session_id);
    }

    /** @test */
    public function courses_and_disciplines_are_globally_scoped_to_the_current_session_and_users_can_use_a_local_scope()
    {
        $oldSession = AcademicSession::factory()->create(['session' => '1980/1981', 'is_default' => true]);
        $newSession = AcademicSession::factory()->create(['session' => '1990/1991', 'is_default' => false]);
        $admin = User::factory()->admin()->create(['academic_session_id' => $oldSession->id]);
        $course1 = Course::factory()->create(['academic_session_id' => $oldSession->id]);
        $course2 = Course::factory()->create(['academic_session_id' => $oldSession->id]);
        $course3 = Course::factory()->create(['academic_session_id' => $newSession->id]);
        $course4 = Course::factory()->create(['academic_session_id' => $newSession->id]);
        $discipline1 = Discipline::factory()->create(['academic_session_id' => $oldSession->id]);
        $discipline2 = Discipline::factory()->create(['academic_session_id' => $oldSession->id]);
        $discipline3 = Discipline::factory()->create(['academic_session_id' => $newSession->id]);
        $discipline4 = Discipline::factory()->create(['academic_session_id' => $newSession->id]);
        $user1 = User::factory()->create(['academic_session_id' => $oldSession->id]);
        $user2 = User::factory()->create(['academic_session_id' => $oldSession->id]);
        $user3 = User::factory()->create(['academic_session_id' => $newSession->id]);
        $user4 = User::factory()->create(['academic_session_id' => $newSession->id]);

        login($admin);
        session(['academic_session' => '1980/1981']);

        $courses = Course::all();
        $this->assertCount(2, $courses);
        $this->assertTrue($courses->contains($course1));
        $this->assertTrue($courses->contains($course2));
        $disciplines = Discipline::all();
        $this->assertCount(2, $disciplines);
        $this->assertTrue($disciplines->contains($discipline1));
        $this->assertTrue($disciplines->contains($discipline2));
        $users = User::forAcademicSession($oldSession)->get();
        $this->assertCount(3, $users);
        $this->assertTrue($users->contains($user1));
        $this->assertTrue($users->contains($user2));
    }

}
