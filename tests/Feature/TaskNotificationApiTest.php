<?php

namespace Tests\Feature;

use App\Events\TaskAssigned;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class TaskNotificationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_task_can_be_created_and_assignment_event_is_dispatched(): void
    {
        Event::fake();

        $assigner = User::factory()->create();
        $assignee = User::factory()->create();

        $response = $this->withHeader('X-User-Id', (string) $assigner->id)
            ->postJson('/api/tasks', [
                'title' => 'Build API',
                'description' => 'Create REST endpoints',
                'assigned_to' => $assignee->id,
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.title', 'Build API');

        $this->assertDatabaseHas('tasks', [
            'title' => 'Build API',
            'assigned_to' => $assignee->id,
        ]);

        Event::assertDispatched(TaskAssigned::class);
    }

    public function test_notifications_endpoint_returns_only_authenticated_user_notifications(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Notification::query()->create([
            'user_id' => $user->id,
            'type' => 'task_assigned',
            'message' => 'Task for user',
        ]);

        Notification::query()->create([
            'user_id' => $otherUser->id,
            'type' => 'task_assigned',
            'message' => 'Task for another user',
        ]);

        $response = $this->withHeader('X-User-Id', (string) $user->id)
            ->getJson('/api/notifications');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertSame('Task for user', $response->json('data.0.message'));
    }

    public function test_notification_can_be_marked_as_read(): void
    {
        $user = User::factory()->create();

        $notification = Notification::query()->create([
            'user_id' => $user->id,
            'type' => 'task_assigned',
            'message' => 'Unread notification',
        ]);

        $this->withHeader('X-User-Id', (string) $user->id)
            ->postJson("/api/notifications/{$notification->id}/read")
            ->assertOk()
            ->assertJsonPath('message', 'Notification marked as read.');

        $this->assertNotNull($notification->fresh()->read_at);
    }
}
