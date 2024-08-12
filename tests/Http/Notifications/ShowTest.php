<?php

declare(strict_types=1);

use App\Models\Question;
use App\Notifications\UserMentioned;
use App\Models\User;

//test('guest', function () {
//    $question = Question::factory()->create([
//        'content' => 'This is the answer',
//    ]);
//
//    $response = $this->get(route('notifications.show', [
//        'username' => $question->from->username,
//        'notification' => $question->to->notifications()->first(),
//    ]));
//
//    $response->assertRedirect(route('login'));
//});
//
//test('notifications about answers are deleted', function () {
//    $question = Question::factory()
//        ->hasAnswer()
//        ->create();
//
//    $question->answer->update(['content' => 'Question answer']);
//
//    $notification = $question->from->notifications()->first();
//    expect($notification->fresh())->not->toBe(null);
//
//    /** @var Illuminate\Testing\TestResponse $response */
//    $response = $this->actingAs($question->from)
//        ->get(route('notifications.show', [
//            'notification' => $notification,
//        ]));
//
//    $response->assertRedirectToRoute('questions.show', ['question' => $question, 'username' => $question->to->username]);
//    expect($notification->fresh())->toBeNull();
//});
//
//test('notifications about questions are not deleted', function () {
//    $question = Question::factory()
//        ->create();
//
//    expect($question->to->notifications()->count())->toBe(1);
//
//    $notification = $question->to->notifications()->first();
//
//    /** @var Illuminate\Testing\TestResponse $response */
//    $response = $this->actingAs($question->to)
//        ->get(route('notifications.show', [
//            'notification' => $notification,
//        ]));
//
//    $response->assertRedirectToRoute('questions.show', ['question' => $question, 'username' => $question->to->username]);
//    expect($notification->fresh())->not->toBeNull();
//});

test('mentioned users who cannot view the question, will delete the notification if clicked', function () {
    $userA = User::factory()->create(
        ['username' => 'johndoe']
    );
    $userB = User::factory()->create();

    $question = Question::factory()->create([
        'to_id' => $userB->id,
        'from_id' => $userB->id,
        'is_update' => true,
        'content' => 'this update is for @johndoe!',
    ]);

    $notification = $userA->notifications->first();

    expect($notification->fresh())->not->toBeNull()
        ->and($userA->notifications()->count())->toBe(1)
        ->and($question->mentions()->count())->toBe(1);

    $response = $this->actingAs($userA)
        ->get(route('notifications.show', [
            'notification' => $notification,
        ]));

    $response
        ->assertRedirect()
        ->assertStatus(302);

    expect($userA->notifications()->count())->toBe(0);
});
