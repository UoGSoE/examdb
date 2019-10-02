<?php

namespace Tests\Unit;

use App\User;
use App\Paper;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Comment;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_paper_can_have_comments_added_to_it()
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
