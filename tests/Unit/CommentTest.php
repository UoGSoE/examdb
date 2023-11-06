<?php

namespace Tests\Unit;

use App\Models\AcademicSession;
use App\Models\Paper;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        AcademicSession::createFirstSession();
    }

    /** @test */
    public function a_paper_can_have_comments_added_to_it(): void
    {
        $user = create(User::class);
        login($user);
        $paper = create(Paper::class);

        $paper->addComment('1st comment');
        $paper->addComment('2nd comment');

        $this->assertCount(2, $paper->comments);
        $this->assertEquals('1st comment', $paper->comments[0]->comment);
        $this->assertEquals('2nd comment', $paper->comments[1]->comment);
    }
}
