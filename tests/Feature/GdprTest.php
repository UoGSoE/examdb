<?php

namespace Tests\Feature;

use App\Paper;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TenantTestCase;
use Tests\TestCase;

class GdprTest extends TenantTestCase
{


    /** @test */
    public function admins_can_export_all_data_about_a_user()
    {
        $admin = create(User::class, ['is_admin' => true]);
        $user = create(User::class);
        $paper1 = create(Paper::class, ['user_id' => $user->id]);
        $paper2 = create(Paper::class, ['user_id' => $user->id]);
        $paper3 = create(Paper::class);
        login($user);
        $paper1->addComment('hello there');

        $response = $this->actingAs($admin)->get(route('gdpr.export.user', $user->id));

        $response->assertOk();
        $response->assertJson([
            'data' => [
                'username' => $user->username,
                'full_name' => $user->full_name,
                'email' => $user->email,
                'papers' => [
                    [
                        'category' => $paper1->category,
                        'subcategory' => $paper1->subcategory,
                        'filename' => $paper1->original_filename,
                        'comments' => [
                            [
                                'comment' => 'hello there',
                            ],
                        ],
                            ],
                    [
                        'category' => $paper2->category,
                        'subcategory' => $paper2->subcategory,
                        'filename' => $paper2->original_filename,
                    ],
                ],
            ],
        ]);
    }

    /** @test */
    public function admin_staff_can_anonymise_an_external_user()
    {
        $admin = create(User::class, ['is_admin' => true]);
        $user = create(User::class, ['is_external' => true, 'username' => 'blah@example.com']);

        $response = $this->actingAs($admin)->post(route('gdpr.anonymise.user', $user->id));

        $response->assertRedirect(route('user.show', $user->id));
        $response->assertSessionDoesntHaveErrors();
        $this->assertEquals('gdpr'.$user->id, $user->fresh()->username);
        $this->assertEquals('gdpr'.$user->id.'@glasgow.ac.uk', $user->fresh()->email);
        $this->assertEquals('anon', $user->fresh()->surname);
        $this->assertEquals('anon', $user->fresh()->forenames);
    }
}
