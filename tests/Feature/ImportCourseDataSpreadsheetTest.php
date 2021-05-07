<?php

namespace Tests\Feature;

use Ldap;
use App\User;
use App\Course;
use Tests\TestCase;
use Ohffs\Ldap\LdapUser;
use Tests\TenantTestCase;
use Mockery\MockInterface;
use App\Jobs\ImportCourseRow;
use Ohffs\Ldap\LdapConnection;
use Illuminate\Http\UploadedFile;
use Ohffs\SimpleSpout\ExcelSheet;
use Ohffs\Ldap\FakeLdapConnection;
use App\Jobs\ImportCourseDataBatch;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Ohffs\Ldap\LdapConnectionInterface;
use App\Mail\CourseImportProcessComplete;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Redis;

class ImportCourseDataSpreadsheetTest extends TenantTestCase
{
    /** @test */
    public function admins_can_see_the_import_page()
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('course.import'));

        $response->assertOk();
        $response->assertSee('Import Course Information');
    }

    /** @test */
    public function admins_can_import_course_info_using_a_spreadsheet_which_fires_a_queued_job_to_do_the_actual_work()
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
    public function when_the_main_data_batch_job_is_dispatched_it_spawns_a_sub_job_for_each_row_in_the_spreadsheet_data()
    {
        $this->withoutExceptionHandling();
        $admin = User::factory()->admin()->create();
        Queue::fake();
        $data = [
            ['Course Code', 'Course Name', 'Discipline', 'Semester', 'Setters', 'Moderators'],
            ['ENG1234', 'Lasers', 'Elec', '1', 'abc1x, trs80y', ' bob1q,lol9s'],
            ['ENG5678', 'Helicopters', 'Bio', '2', 'cde1x,pop80y', ' bob1q,trs80y'],
        ];

        ImportCourseDataBatch::dispatchNow($data, $admin->id);

        Queue::assertPushed(ImportCourseRow::class, 3);
    }

    /** @test */
    public function the_import_course_row_job_actually_creates_records_for_the_data()
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

        $data = [
            ['Course Code', 'Course Name', 'Discipline', 'Semester', 'Setters', 'Moderators', 'Examined?'],
            ['ENG1234', 'Lasers', 'Elec', '1', 'abc1x, trs80y', ' bob1q,lol9s', 'N'],
            ['ENG5678', 'Helicopters', 'Bio', '2', 'cde1x,pop80y', ' bob1q,trs80y', 'Y'],
        ];

        ImportCourseRow::dispatch($data[1], 1);

        $this->assertDatabaseHas('courses', ['code' => 'ENG1234', 'title' => 'Lasers', 'semester' => 1, 'is_examined' => false]);
        $this->assertDatabaseHas('disciplines', ['title' => 'Elec']);

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
    public function importing_duplicates_or_already_existing_data_updates_it_rather_than_creating_duplicates()
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
            ['ENG1234', 'Lasers', 'Elec', '1', 'abc1x, trs80y', ' bob1q,lol9s,abc1x ', 'Y'],
            ['ENG5678', 'Helicopters', 'Bio', '2', 'cde1x,pop80y', ' bob1q,trs80y', 'N'],
        ];

        ImportCourseRow::dispatch($data[1], 1);

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
    public function once_the_data_has_been_imported_an_email_is_sent_to_the_original_user_to_let_them_know()
    {
        Mail::fake();
        $admin = User::factory()->admin()->create();
        $this->fakeLdapConnection();
        // errors mock - five invalid GUIDs
        Redis::shouldReceive('sadd')->times(5)->andReturn(true);
        $this->fakeRedisErrors();
        ImportCourseDataBatch::dispatch([
            ['ENG1234', 'Lasers', 'Elec', '1', 'abc1x, trs80y', ' bob1q,lol9s,abc1x ', 'Y'],
        ], $admin->id);

        Mail::assertQueued(CourseImportProcessComplete::class, 1);
        Mail::assertQueued(CourseImportProcessComplete::class, function ($mail) use ($admin) {
            return $mail->hasTo($admin);
        });

        \Mockery::close();
    }

    /** @test */
    public function any_errors_during_the_import_are_stored_in_redis()
    {
        Mail::fake();
        $admin = User::factory()->admin()->create();
        $this->fakeLdapConnection();
        // two invalid GUIDs and one missing course code
        Redis::shouldReceive('sadd')->times(3)->andReturn(true);
        $this->fakeRedisErrors();

        ImportCourseDataBatch::dispatch([
            ['ENG1234', 'Lasers', 'Elec', '1', 'abc1x', ' bob1q', 'Y'],
            ['', 'Lasers', 'Elec', '1', 'abc1x', ' bob1q', 'N'],
        ], $admin->id);

        Mail::assertQueued(CourseImportProcessComplete::class, function ($mail) use ($admin) {
            return $mail->hasTo($admin);
        });

        \Mockery::close();
    }

    /** @test */
    public function any_errors_are_pulled_from_redis_and_passed_to_the_email()
    {
        Mail::fake();
        $admin = User::factory()->admin()->create();
        $this->fakeLdapConnection();
        // two invalid GUIDs and one missing course code
        Redis::shouldReceive('sadd')->times(3)->andReturn(true);
        $this->fakeRedisErrors();

        ImportCourseDataBatch::dispatch([
            ['ENG1234', 'Lasers', 'Elec', '1', 'abc1x', ' bob1q', 'Y'],
            ['', 'Lasers', 'Elec', '1', 'abc1x', ' bob1q', 'N'],
        ], $admin->id);

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
