<?php

namespace Tests\Unit;

use App\User;
use App\Paper;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Solution;
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

    /** @test */
    public function a_solution_can_have_comments_added_to_it()
    {
        $user = create(User::class);
        login($user);
        $solution = create(Solution::class);

        $solution->addComment('1st type', '1st comment');
        $solution->addComment('2nd type', '2nd comment');

        $this->assertCount(2, $solution->comments);
        $this->assertEquals('1st type', $solution->comments[0]->category);
        $this->assertEquals('1st comment', $solution->comments[0]->comment);
        $this->assertEquals('2nd type', $solution->comments[1]->category);
        $this->assertEquals('2nd comment', $solution->comments[1]->comment);
    }

    /** @test */
    public function comments_are_polymorphic()
    {
        $user = create(User::class);
        login($user);
        $solution = create(Solution::class);
        $paper = create(Paper::class);

        $solution->addComment('1st type', '1st comment');
        $paper->addComment('2nd type', '2nd comment');

        $this->assertCount(2, Comment::all());
        $this->assertCount(1, $solution->comments);
        $this->assertCount(1, $paper->comments);
    }

}
