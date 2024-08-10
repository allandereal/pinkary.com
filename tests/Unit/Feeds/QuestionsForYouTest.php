<?php

declare(strict_types=1);

use App\Models\Like;
use App\Models\Question;
use App\Models\User;
use App\Queries\Feeds\QuestionsForYouFeed;
use Illuminate\Database\Eloquent\Builder;

it('render questions with right conditions', function () {
    $likerUser = User::factory()->create();

    $userTo = User::factory()->create();

    $questionWithLike = Question::factory()->create([
        'to_id' => $userTo->id,
        'content' => 'Question content',
        'is_update' => true,
        'is_reported' => false,
    ]);

    Like::factory()->create([
        'user_id' => $likerUser->id,
        'question_id' => $questionWithLike->id,
    ]);

    Question::factory()
        ->hasAnswer()
        ->create([
            'to_id' => $userTo->id,
            'content' => 'Question content 2',
            'is_reported' => false,
        ]);

    $builder = (new QuestionsForYouFeed($likerUser))->builder();

    expect($builder->count())->toBe(2);
});

it('do not render questions liked beyond the last 60 days', function () {
    $likerUser = User::factory()->create();

    $userTo = User::factory()->create();

    $questionWithLike = Question::factory()->create([
        'to_id' => $userTo->id,
        'content' => 'Answer',
        'is_update' => true,
        'is_reported' => false,
    ]);

    Like::factory()->create([
        'user_id' => $likerUser->id,
        'question_id' => $questionWithLike->id,
        'created_at' => now()->subDays(90),
    ]);

    Question::factory()->create([
        'to_id' => $userTo->id,
        'content' => 'Answer 2',
        'is_update' => true,
        'is_reported' => false,
    ]);

    $builder = (new QuestionsForYouFeed($likerUser))->builder();

    expect($builder->count())->toBe(0);
});

it('do not render questions without answer', function () {
    $likerUser = User::factory()->create();

    $userTo = User::factory()->create();

    $content = 'Question to the question that needs to be rendered';

    $questionWithLike = Question::factory()
        ->hasAnswer()
        ->create([
        'to_id' => $userTo->id,
        'content' => $content,
        'is_reported' => false,
    ]);

    Like::factory()->create([
        'user_id' => $likerUser->id,
        'question_id' => $questionWithLike->id,
    ]);

    Question::factory()->create([
        'to_id' => $userTo->id,
        'is_reported' => false,
    ]);

    $builder = (new QuestionsForYouFeed($likerUser))->builder();

    expect($builder->where('content', $content)->count())->toBe(1);
});

it('includes questions made to users i follow', function () {
    $user = User::factory()->create();

    $follower = User::factory()->create();

    $follower->following()->attach($user);

    Question::factory()
        ->hasAnswer(['content' => 'Answer'])
        ->create([
            'to_id' => $user->id,
        ]);

    $builder = (new QuestionsForYouFeed($follower))->builder();

    expect($builder->count())->toBe(1);
});

it('do not render reported questions', function () {
    $likerUser = User::factory()->create();

    $userTo = User::factory()->create();

    $questionWithLike = Question::factory()->create([
        'to_id' => $userTo->id,
        'is_update' => true,
        'is_reported' => false,
    ]);

    Like::factory()->create([
        'user_id' => $likerUser->id,
        'question_id' => $questionWithLike->id,
    ]);

    Question::factory()->create([
        'to_id' => $userTo->id,
        'is_update' => true,
        'is_reported' => true,
    ]);

    $builder = (new QuestionsForYouFeed($likerUser))->builder();

    expect($builder->where('is_reported', false)->count())->toBe(1);
});

it('builder returns Eloquent\Builder instance', function () {
    $builder = (new QuestionsForYouFeed(User::factory()->create()))->builder();

    expect($builder)->toBeInstanceOf(Builder::class);
});
