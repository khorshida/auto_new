<?php

use App\Enums\InquiryStatus;
use App\Enums\InquiryType;
use App\Enums\TaskStatus;
use App\Models\Car;
use App\Models\Inquiry;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('a guest is sent to the sign-in page from the crm', function () {
    $this->get(route('admin.crm'))
        ->assertRedirect(route('login'));

    Livewire::test('pages::admin.crm')
        ->assertForbidden();
});

test('the crm shows saved leads and cars without a booking calendar', function () {
    $user = User::factory()->create();
    $car = Car::factory()->create(['name' => 'BMW i5']);
    Inquiry::factory()->for($car)->create([
        'name' => 'Md',
        'type' => InquiryType::TestDrive,
        'status' => InquiryStatus::New,
    ]);

    $this->actingAs($user)
        ->get(route('admin.crm'))
        ->assertOk()
        ->assertSee('Overview')
        ->assertSee('Md')
        ->assertSee('BMW i5')
        ->assertSee('Nobody is assigned yet')
        ->assertDontSee('booked');

    Livewire::actingAs($user)
        ->test('pages::admin.crm')
        ->call('show', 'cal')
        ->assertSee('does not mark a day as taken')
        ->call('show', 'ann')
        ->assertSee('It is not sent, and it is not saved.')
        ->call('show', 'stock')
        ->assertSee('BMW i5')
        ->call('show', 'team')
        ->assertSee('Not saved');
});

test('the crm can move a saved lead to contacted', function () {
    $user = User::factory()->create();
    $inquiry = Inquiry::factory()->create([
        'name' => 'Md',
        'status' => InquiryStatus::New,
    ]);

    Livewire::actingAs($user)
        ->test('pages::admin.crm')
        ->call('show', 'leads')
        ->call('updateStatus', $inquiry->id, 'contacted')
        ->call('updateStatus', $inquiry->id, 'not-a-status');

    expect($inquiry->refresh()->status)->toBe(InquiryStatus::Contacted);
});

test('the signed-in admin can add a task and mark it done', function () {
    $user = User::factory()->create();
    $inquiry = Inquiry::factory()->create(['name' => 'Md']);

    Livewire::actingAs($user)
        ->test('pages::admin.crm')
        ->set('taskTitle', 'Call Md back')
        ->set('taskNotes', 'Ask about the colour')
        ->set('taskDue', '2026-11-12')
        ->set('taskInquiryId', (string) $inquiry->id)
        ->call('saveTask')
        ->assertHasNoErrors()
        ->call('show', 'tasks')
        ->assertSee('Call Md back')
        ->call('completeTask', Task::query()->first()->id)
        ->set('taskFilter', 'done')
        ->assertSee('Call Md back')
        ->assertSee('Task marked done.');

    $task = Task::query()->first();

    expect($task->status)->toBe(TaskStatus::Done)
        ->and($task->created_by)->toBe($user->id)
        ->and($task->assigned_to)->toBe($user->id)
        ->and($task->inquiry_id)->toBe($inquiry->id)
        ->and($task->notes)->toBe('Ask about the colour');
});

test('removing a lead keeps the task', function () {
    $user = User::factory()->create();
    $inquiry = Inquiry::factory()->create();
    $task = Task::factory()->create([
        'created_by' => $user->id,
        'assigned_to' => $user->id,
        'inquiry_id' => $inquiry->id,
    ]);

    $inquiry->delete();

    expect($task->refresh()->inquiry_id)->toBeNull()
        ->and(Task::query()->whereKey($task->id)->exists())->toBeTrue();
});

test('removing the staff user does not remove their tasks', function () {
    $user = User::factory()->create();
    $task = Task::factory()->create([
        'created_by' => $user->id,
        'assigned_to' => $user->id,
    ]);

    expect(fn () => $user->delete())->toThrow(QueryException::class);

    expect(Task::query()->whereKey($task->id)->exists())->toBeTrue();
});
