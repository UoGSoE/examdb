<?php

namespace Tests\Feature;

use App\Jobs\ImportCourseDataBatch;
use App\Jobs\ImportCourseRow;
use App\Mail\CourseImportProcessComplete;
use App\Models\AcademicSession;
use App\Models\Course;
use App\Models\User;
use App\Scopes\CurrentAcademicSessionScope;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;
use Ohffs\Ldap\FakeLdapConnection;
use Ohffs\Ldap\LdapConnectionInterface;
use Ohffs\Ldap\LdapUser;
use Ohffs\SimpleSpout\ExcelSheet;
use Tests\TestCase;

class ImportCourseDataSpreadsheetTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        AcademicSession::createFirstSession();
    }

    /** @test */
    public function admins_can_see_the_import_page(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('course.import'));

        $response->assertOk();
        $response->assertSee('Import Course Information');
    }

    /** @test */
    public function admins_can_import_course_info_using_a_spreadsheet_which_fires_a_queued_job_to_do_the_actual_work(): void
    {
        $this->withoutExceptionHandling();
        $admin = User::factory()->admin()->create();

        Queue::fake();
        // \Ldap::shouldReceive('findUser')->with('abc1x')->andReturn(new LdapUser([
        //     [
        //     'uid' => ['abc1x'],
        //     'mail' => ['abc@example.com'],
        //     'sn' => ['smith'],
        //     'givenname' => ['jenny'],
        //     'telephonenumber' => ['12345'],
        //     ],
        // ]));

        $data = [
            ['Course Code', 'Course Name', 'Discipline', 'Semester', 'Setters', 'Moderators'],
            ['ENG1234', 'Lasers', 'Elec', '1', 'abc1x, trs80y', ' bob1q,lol9s'],
            ['ENG5678', 'Helicopters', 'Bio', '2', 'cde1x,pop80y', ' bob1q,trs80y'],
        ];
        $sheet = (new ExcelSheet)->generate($data);

        $response = $this->actingAs($admin)->post(route('course.import.store'), [
            'sheet' => new UploadedFile($sheet, 'sheet.xlsx', null, null, true),
        ]);

        $response->assertRedirect(route('course.index'));
        $response->assertSessionHas('success');
        Queue::assertPushed(ImportCourseDataBatch::class, function ($job) use ($admin) {
            return count($job->spreadsheetData) === 3 &&
                $job->userId === $admin->id;
        });
    }

    /** @test */
    public function when_the_main_data_batch_job_is_dispatched_it_spawns_a_sub_job_for_each_row_in_the_spreadsheet_data(): void
    {
        $this->withoutExceptionHandling();
        $admin = User::factory()->admin()->create();
        Queue::fake();
        $data = [
            ['Course Code', 'Course Name', 'Discipline', 'Semester', 'Setters GUIDs', 'Setters Names', 'Moderators GUIDs', 'Moderators Names', 'Externals Emails', 'Externals Names', 'Examined?'],
            ['ENG1234', 'Lasers', 'Elec', '1', 'abc1x, trs80y', 'Jim Smith, Tina Smith', ' bob1q,lol9s', 'Bob Jones, Lola McVitie', 'someone@example.com', 'Some One', 'N'],
            ['ENG5678', 'Helicopters', 'Bio', '2', 'cde1x,pop80y', 'Carol Exmouth, Poppy Flower', ' bob1q,trs80y', 'Bob Jones, Tina Smith', 'fran@example.com', 'Fran Smith', 'Y'],
        ];

        ImportCourseDataBatch::dispatchSync($data, $admin->id, $admin->getCurrentAcademicSession()->id);

        Queue::assertPushed(ImportCourseRow::class, 3);
    }

    /** @test */
    public function the_import_course_row_job_actually_creates_records_for_the_data(): void
    {
        $this->withoutExceptionHandling();
        $admin = User::factory()->admin()->create();
        $this->fakeLdapConnection();
        \Ldap::shouldReceive('findUser')->with('abc1x')->andReturn(new LdapUser([
            [
                'uid' => ['abc1x'],
                'mail' => ['abc@example.com'],
                'sn' => ['smith'],
                'givenname' => ['jenny'],
                'telephonenumber' => ['12345'],
            ],
        ]));
        \Ldap::shouldReceive('findUser')->with('trs80y')->andReturn(new LdapUser([
            [
                'uid' => ['trs80y'],
                'mail' => ['trs@example.com'],
                'sn' => ['blah'],
                'givenname' => ['whatever'],
                'telephonenumber' => ['12345'],
            ],
        ]));
        \Ldap::shouldReceive('findUser')->with('bob1q')->andReturn(new LdapUser([
            [
                'uid' => ['bob1q'],
                'mail' => ['bob@example.com'],
                'sn' => ['blah blah'],
                'givenname' => ['whatever whatever'],
                'telephonenumber' => ['12345'],
            ],
        ]));
        \Ldap::shouldReceive('findUser')->with('lol9s')->andReturn(new LdapUser([
            [
                'uid' => ['lol9s'],
                'mail' => ['lol@example.com'],
                'sn' => ['fruit'],
                'givenname' => ['sundae'],
                'telephonenumber' => ['12345'],
            ],
        ]));
        \Ldap::shouldReceive('findUser')->with('cde1x')->andReturn(new LdapUser([
            [
                'uid' => ['cde1x'],
                'mail' => ['cde1x@example.com'],
                'sn' => ['fruit'],
                'givenname' => ['sundae'],
                'telephonenumber' => ['12345'],
            ],
        ]));
        \Ldap::shouldReceive('findUser')->with('pop80y')->andReturn(new LdapUser([
            [
                'uid' => ['pop80y'],
                'mail' => ['pop80y@example.com'],
                'sn' => ['fruit'],
                'givenname' => ['sundae'],
                'telephonenumber' => ['12345'],
            ],
        ]));

        $data = [
            ['Course Code', 'Course Name', 'Discipline', 'Semester', 'Setters GUIDs', 'Setters Names', 'Moderators GUIDs', 'Moderators Names', 'Externals Emails', 'Externals Names', 'Examined?'],
            ['ENG1234', 'Lasers', 'Elec', '1', 'abc1x, trs80y', 'Jim Smith, Tina Smith', ' bob1q,lol9s', 'Bob Jones, Lola McVitie', 'someone@example.com', 'Some One', 'N'],
            ['ENG5678', 'Helicopters', 'Bio', '2', 'cde1x,pop80y', 'Carol Exmouth, Poppy Flower', ' bob1q,trs80y', 'Bob Jones, Tina Smith', 'fran@example.com', 'Fran Smith', 'Y'],
            ['ENG5679', 'Rockets', 'Bio', '2', 'cde1x,pop80y', 'Carol Exmouth, Poppy Flower', ' bob1q,trs80y', 'Bob Jones, Tina Smith', 'fran@example.com', 'Fran Smith', 'Y'],
        ];

        ImportCourseRow::dispatch($data[1], 1, $admin->getCurrentAcademicSession()->id);

        $academicSession = AcademicSession::firstOrFail();
        $this->assertDatabaseHas('courses', ['code' => 'ENG1234', 'title' => 'Lasers', 'semester' => 1, 'is_examined' => false, 'academic_session_id' => $academicSession->id]);
        $this->assertDatabaseHas('disciplines', ['title' => 'Elec', 'academic_session_id' => $academicSession->id]);

        $this->assertDatabaseHas('users', ['username' => 'abc1x', 'academic_session_id' => $academicSession->id]);
        $this->assertTrue(Course::first()->setters->contains(User::findByUsername('abc1x')));
        $this->assertDatabaseHas('users', ['username' => 'trs80y', 'academic_session_id' => $academicSession->id]);
        $this->assertTrue(Course::first()->setters->contains(User::findByUsername('trs80y')));

        $this->assertDatabaseHas('users', ['username' => 'bob1q', 'academic_session_id' => $academicSession->id]);
        $this->assertTrue(Course::first()->moderators->contains(User::findByUsername('bob1q')));
        $this->assertDatabaseHas('users', ['username' => 'lol9s', 'academic_session_id' => $academicSession->id]);
        $this->assertTrue(Course::first()->moderators->contains(User::findByUsername('lol9s')));

        // 2nd row of data
        ImportCourseRow::dispatch($data[2], 1, $admin->getCurrentAcademicSession()->id);

        $academicSession = AcademicSession::firstOrFail();
        $this->assertDatabaseHas('courses', ['code' => 'ENG5678', 'title' => 'Helicopters', 'semester' => 2, 'is_examined' => true, 'academic_session_id' => $academicSession->id]);
        $this->assertDatabaseHas('disciplines', ['title' => 'Bio', 'academic_session_id' => $academicSession->id]);

        $this->assertDatabaseHas('users', ['username' => 'cde1x', 'academic_session_id' => $academicSession->id]);
        $this->assertDatabaseHas('users', ['username' => 'pop80y', 'academic_session_id' => $academicSession->id]);

        // 3rd row of data
        ImportCourseRow::dispatch($data[3], 1, $admin->getCurrentAcademicSession()->id);

        $academicSession = AcademicSession::firstOrFail();
        $this->assertDatabaseHas('courses', ['code' => 'ENG5679', 'title' => 'Rockets', 'semester' => 2, 'is_examined' => true, 'academic_session_id' => $academicSession->id]);
        $this->assertDatabaseHas('disciplines', ['title' => 'Bio', 'academic_session_id' => $academicSession->id]);
        $this->assertDatabaseCount('disciplines', 2);

        \Mockery::close();
    }

    /** @test */
    public function the_import_sets_all_created_records_academic_session_id_to_the_correct_academic_session(): void
    {
        $this->withoutExceptionHandling();
        $admin = User::factory()->admin()->create();
        $session2 = AcademicSession::factory()->create(['session' => '1990/1991']);

        $this->fakeLdapConnection();
        \Ldap::shouldReceive('findUser')->with('abc1x')->andReturn(new LdapUser([
            [
                'uid' => ['abc1x'],
                'mail' => ['abc@example.com'],
                'sn' => ['smith'],
                'givenname' => ['jenny'],
                'telephonenumber' => ['12345'],
            ],
        ]));
        \Ldap::shouldReceive('findUser')->with('trs80y')->andReturn(new LdapUser([
            [
                'uid' => ['trs80y'],
                'mail' => ['trs@example.com'],
                'sn' => ['blah'],
                'givenname' => ['whatever'],
                'telephonenumber' => ['12345'],
            ],
        ]));
        \Ldap::shouldReceive('findUser')->with('bob1q')->andReturn(new LdapUser([
            [
                'uid' => ['bob1q'],
                'mail' => ['bob@example.com'],
                'sn' => ['blah blah'],
                'givenname' => ['whatever whatever'],
                'telephonenumber' => ['12345'],
            ],
        ]));
        \Ldap::shouldReceive('findUser')->with('lol9s')->andReturn(new LdapUser([
            [
                'uid' => ['lol9s'],
                'mail' => ['lol@example.com'],
                'sn' => ['fruit'],
                'givenname' => ['sundae'],
                'telephonenumber' => ['12345'],
            ],
        ]));

        $data = [
            ['Course Code', 'Course Name', 'Discipline', 'Semester', 'Setters GUIDs', 'Setters Names', 'Moderators GUIDs', 'Moderators Names', 'Externals Emails', 'Externals Names', 'Examined?'],
            ['ENG1234', 'Lasers', 'Elec', '1', 'abc1x, trs80y', 'Jim Smith, Tina Smith', ' bob1q,lol9s', 'Bob Jones, Lola McVitie', 'someone@example.com', 'Some One', 'N'],
            ['ENG5678', 'Helicopters', 'Bio', '2', 'cde1x,pop80y', 'Carol Exmouth, Poppy Flower', ' bob1q,trs80y', 'Bob Jones, Tina Smith', 'fran@example.com', 'Fran Smith', 'Y'],
        ];

        ImportCourseRow::dispatch($data[1], 1, $session2->id);

        $academicSession = AcademicSession::firstOrFail();
        session(['academic_session' => $academicSession->session]);

        $this->assertDatabaseHas('courses', ['code' => 'ENG1234', 'title' => 'Lasers', 'semester' => 1, 'is_examined' => false, 'academic_session_id' => $session2->id]);
        $this->assertDatabaseHas('disciplines', ['title' => 'Elec', 'academic_session_id' => $session2->id]);

        $course = Course::withoutGlobalScope(CurrentAcademicSessionScope::class)->with([
            'setters' => fn ($query) => $query->withoutGlobalScope(CurrentAcademicSessionScope::class),
            'moderators' => fn ($query) => $query->withoutGlobalScope(CurrentAcademicSessionScope::class),
        ])->first();
        $this->assertDatabaseHas('users', ['username' => 'abc1x', 'academic_session_id' => $session2->id]);
        $this->assertTrue($course->setters->contains(User::withoutGlobalScope(CurrentAcademicSessionScope::class)->where('username', '=', 'abc1x')->firstOrFail()));
        $this->assertDatabaseHas('users', ['username' => 'trs80y', 'academic_session_id' => $session2->id]);
        $this->assertTrue($course->setters->contains(User::withoutGlobalScope(CurrentAcademicSessionScope::class)->where('username', '=', 'trs80y')->firstOrFail()));

        $this->assertDatabaseHas('users', ['username' => 'bob1q', 'academic_session_id' => $session2->id]);
        $this->assertTrue($course->moderators->contains(User::withoutGlobalScope(CurrentAcademicSessionScope::class)->where('username', '=', 'bob1q')->firstOrFail()));
        $this->assertDatabaseHas('users', ['username' => 'lol9s', 'academic_session_id' => $session2->id]);
        $this->assertTrue($course->moderators->contains(User::withoutGlobalScope(CurrentAcademicSessionScope::class)->where('username', '=', 'lol9s')->firstOrFail()));

        // import the row again to make sure we don't get duplicates
        ImportCourseRow::dispatch($data[1], 1, $session2->id);

        $this->assertDatabaseCount('courses', 1);
        $this->assertDatabaseCount('disciplines', 1);
        $this->assertDatabaseCount('users', 5); // four spreadsheet users, one original admin user

        \Mockery::close();
    }

    /** @test */
    public function if_a_row_is_missing_key_data_an_error_is_recorded(): void
    {
        Redis::shouldReceive('sadd')
            ->once()
            ->with('-errors', 'Invalid data on row 1 : Row is missing key data and is less than 4 columns')
            ->andReturn(true);

        ImportCourseRow::dispatch(['ABC1234', 'Lasers'], 1, AcademicSession::first()->id);

        $this->assertEquals(0, Course::count());
        \Mockery::close();
    }

    /** @test */
    public function importing_duplicates_or_already_existing_data_updates_it_rather_than_creating_duplicates(): void
    {
        $this->withoutExceptionHandling();
        $admin = User::factory()->admin()->create();
        $this->fakeLdapConnection();
        \Ldap::shouldReceive('findUser')->with('abc1x')->andReturn(new LdapUser([
            [
                'uid' => ['abc1x'],
                'mail' => ['abc@example.com'],
                'sn' => ['smith'],
                'givenname' => ['jenny'],
                'telephonenumber' => ['12345'],
            ],
        ]));
        \Ldap::shouldReceive('findUser')->with('trs80y')->andReturn(new LdapUser([
            [
                'uid' => ['trs80y'],
                'mail' => ['trs@example.com'],
                'sn' => ['blah'],
                'givenname' => ['whatever'],
                'telephonenumber' => ['12345'],
            ],
        ]));
        \Ldap::shouldReceive('findUser')->with('bob1q')->andReturn(new LdapUser([
            [
                'uid' => ['bob1q'],
                'mail' => ['bob@example.com'],
                'sn' => ['blah blah'],
                'givenname' => ['whatever whatever'],
                'telephonenumber' => ['12345'],
            ],
        ]));
        \Ldap::shouldReceive('findUser')->with('lol9s')->andReturn(new LdapUser([
            [
                'uid' => ['lol9s'],
                'mail' => ['lol@example.com'],
                'sn' => ['fruit'],
                'givenname' => ['sundae'],
                'telephonenumber' => ['12345'],
            ],
        ]));
        $existingCourse = Course::factory()->create(['code' => 'ENG1234', 'title' => 'Fred', 'semester' => 3]);
        $existingUser = User::factory()->create(['username' => 'abc1x']);
        $data = [
            ['Course Code', 'Course Name', 'Discipline', 'Semester', 'Setters', 'Moderators', 'Examined?'],
            ['ENG1234', 'Lasers', 'Elec', '1', 'abc1x, trs80y', 'Jim Smith, Tina Smith', ' bob1q,lol9s', 'Bob Jones, Lola McVitie', 'someone@example.com', 'Some One', 'N'],
            ['ENG5678', 'Helicopters', 'Elec', '2', 'cde1x,pop80y', 'Carol Exmouth, Poppy Flower', ' bob1q,trs80y', 'Bob Jones, Tina Smith', 'fran@example.com', 'Fran Smith', 'Y'],
        ];

        ImportCourseRow::dispatch($data[1], 1, $admin->getCurrentAcademicSession()->id);

        $this->assertEquals(1, Course::count());

        $this->assertDatabaseHas('courses', ['code' => 'ENG1234', 'title' => 'Lasers', 'semester' => 1]);
        $this->assertDatabaseHas('disciplines', ['title' => 'Elec']);

        $this->assertEquals(5, User::count()); // admin user + four from spreadsheet row data

        $this->assertDatabaseHas('users', ['username' => 'abc1x']);
        $this->assertTrue(Course::first()->setters->contains(User::findByUsername('abc1x')));
        $this->assertDatabaseHas('users', ['username' => 'trs80y']);
        $this->assertTrue(Course::first()->setters->contains(User::findByUsername('trs80y')));

        $this->assertDatabaseHas('users', ['username' => 'bob1q']);
        $this->assertTrue(Course::first()->moderators->contains(User::findByUsername('bob1q')));
        $this->assertDatabaseHas('users', ['username' => 'lol9s']);
        $this->assertTrue(Course::first()->moderators->contains(User::findByUsername('lol9s')));

        \Mockery::close();
    }

    /** @test */
    public function once_the_data_has_been_imported_an_email_is_sent_to_the_original_user_to_let_them_know(): void
    {
        Mail::fake();
        $admin = User::factory()->admin()->create();
        $this->fakeLdapConnection();
        // errors mock - five invalid GUIDs
        Redis::shouldReceive('sadd')->times(5)->andReturn(true);
        $this->fakeRedisErrors();
        ImportCourseDataBatch::dispatch([
            ['ENG1234', 'Lasers', 'Elec', '1', 'abc1x, trs80y', 'Jim Smith, Tina Smith', ' bob1q,lol9s,abc1x', 'Bob Jones, Lola McVitie, Anne Chalmers', 'someone@example.com', 'Some One', 'Y'],
        ], $admin->id, $admin->getCurrentAcademicSession()->id);

        Mail::assertQueued(CourseImportProcessComplete::class, 1);
        Mail::assertQueued(CourseImportProcessComplete::class, function ($mail) use ($admin) {
            return $mail->hasTo($admin);
        });

        \Mockery::close();
    }

    /** @test */
    public function any_errors_during_the_import_are_stored_in_redis(): void
    {
        Mail::fake();
        $admin = User::factory()->admin()->create();
        $this->fakeLdapConnection();
        // two invalid GUIDs and one missing course code
        Redis::shouldReceive('sadd')->times(3)->andReturn(true);
        $this->fakeRedisErrors();

        ImportCourseDataBatch::dispatch([
            ['ENG1234', 'Lasers', 'Elec', '1', 'abc1x', 'Jim Smith', ' bob1q', 'Bob Jones', 'someone@example.com', 'Some One', 'Y'],
            ['', 'Lasers', 'Elec', '1', 'abc1x', 'Jim Smith', ' bob1q', 'Bob Jones', 'someone@example.com', 'Some One', 'N'],
        ], $admin->id, $admin->getCurrentAcademicSession()->id);

        Mail::assertQueued(CourseImportProcessComplete::class, function ($mail) use ($admin) {
            return $mail->hasTo($admin);
        });

        \Mockery::close();
    }

    /** @test */
    public function any_errors_are_pulled_from_redis_and_passed_to_the_email(): void
    {
        Mail::fake();
        $admin = User::factory()->admin()->create();
        $this->fakeLdapConnection();
        // two invalid GUIDs and one missing course code
        Redis::shouldReceive('sadd')->times(3)->andReturn(true);
        $this->fakeRedisErrors();

        ImportCourseDataBatch::dispatch([
            ['ENG1234', 'Lasers', 'Elec', '1', 'abc1x', 'Jim Smith', ' bob1q', 'Bob Jones', 'someone@example.com', 'Some One', 'Y'],
            ['', 'Lasers', 'Elec', '1', 'abc1x', 'Jim Smith', ' bob1q', 'Bob Jones', 'someone@example.com', 'Some One', 'N'],
        ], $admin->id, $admin->getCurrentAcademicSession()->id);

        Mail::assertQueued(CourseImportProcessComplete::class, function ($mail) use ($admin) {
            return $mail->hasTo($admin) &&
                count($mail->errors) === 3;
        });

        \Mockery::close();
    }

    private function fakeLdapConnection()
    {
        $this->instance(
            LdapConnectionInterface::class,
            new FakeLdapConnection('up', 'whatever')
        );
    }

    public function fakeRedisErrors()
    {
        Redis::shouldReceive('smembers')->times(1)->andReturn(['error 1', 'error 2', 'error 3']);
        Redis::shouldReceive('del')->times(1)->andReturn(true);
    }
}
