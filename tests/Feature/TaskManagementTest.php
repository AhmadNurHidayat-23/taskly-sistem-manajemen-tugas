<?php

namespace Tests\Feature;

use App\Models\Task;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class TaskManagementTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_creates_a_task_and_ignores_unexpected_attributes(): void
    {
        $data = ['title' => 'Write the brief', 'description' => 'A clear direction.', 'due_date' => '2026-11-05', 'priority' => 'high', 'status' => 'todo'];

        $response = $this->post(route('tasks.store'), $data + ['id' => 999, 'created_at' => '2000-01-01']);

        $task = Task::firstOrFail();
        $response->assertRedirectToRoute('tasks.show', $task)->assertSessionHas('success');
        $this->assertDatabaseHas('tasks', array_replace($data, ['due_date' => '2026-11-05 00:00:00']));
        $this->assertNotSame(999, $task->id);
        $this->assertNotSame('2000-01-01', $task->created_at->toDateString());
    }

    public function test_accepts_a_task_without_optional_fields(): void
    {
        $response = $this->post(route('tasks.store'), ['title' => 'A small step', 'priority' => 'low', 'status' => 'in_progress']);

        $response->assertSessionHasNoErrors()->assertRedirect();
        $this->assertDatabaseHas('tasks', ['title' => 'A small step', 'description' => null, 'due_date' => null]);
    }

    public function test_edits_all_task_fields_and_can_clear_optional_fields(): void
    {
        $task = Task::factory()->create();
        $data = ['title' => 'Updated title', 'description' => '', 'due_date' => '', 'priority' => 'low', 'status' => 'completed'];

        $this->put(route('tasks.update', $task), $data)->assertRedirectToRoute('tasks.show', $task)->assertSessionHasNoErrors();

        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'title' => 'Updated title', 'description' => null, 'due_date' => null, 'priority' => 'low', 'status' => 'completed']);
    }

    #[TestWith(['todo'])]
    #[TestWith(['in_progress'])]
    #[TestWith(['completed'])]
    public function test_updates_status_without_changing_task_content(string $status): void
    {
        $task = Task::factory()->create(['title' => 'Keep this title']);

        $this->from(route('tasks.index'))->patch(route('tasks.status', $task), ['status' => $status, 'title' => 'Unexpected change'])->assertRedirectToRoute('tasks.index');

        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'status' => $status, 'title' => 'Keep this title']);
    }

    public function test_deletes_a_task(): void
    {
        $task = Task::factory()->create();

        $this->delete(route('tasks.destroy', $task))->assertRedirectToRoute('tasks.index')->assertSessionHas('success');

        $this->assertModelMissing($task);
    }

    #[DataProvider('invalidInputs')]
    public function test_rejects_invalid_task_data_without_saving(string $field, mixed $value, string $message): void
    {
        $data = ['title' => 'Valid task', 'description' => 'Notes', 'due_date' => null, 'priority' => 'medium', 'status' => 'todo'];
        $data[$field] = $value;

        $this->from(route('tasks.create'))->post(route('tasks.store'), $data)
            ->assertRedirectToRoute('tasks.create')->assertSessionHasErrors([$field => $message]);

        $this->assertDatabaseCount('tasks', 0);
    }

    public static function invalidInputs(): array
    {
        return [
            'empty title' => ['title', '  ', 'The title field is required.'],
            'title limit' => ['title', str_repeat('x', 256), 'The title field must not be greater than 255 characters.'],
            'title type' => ['title', ['invalid'], 'The title field must be a string.'],
            'description limit' => ['description', str_repeat('x', 10001), 'The description field must not be greater than 10000 characters.'],
            'description type' => ['description', ['invalid'], 'The description field must be a string.'],
            'invalid date' => ['due_date', '2026-02-30', 'The due date field must match the format Y-m-d.'],
            'invalid priority' => ['priority', 'urgent', 'The selected priority is invalid.'],
            'missing priority' => ['priority', '', 'The priority field is required.'],
            'invalid status' => ['status', 'archived', 'The selected status is invalid.'],
            'missing status' => ['status', '', 'The status field is required.'],
        ];
    }

    public function test_invalid_edits_and_status_changes_leave_task_unchanged(): void
    {
        $task = Task::factory()->create(['title' => 'Original task', 'status' => 'todo']);

        $this->put(route('tasks.update', $task), ['title' => ''])->assertSessionHasErrors('title');
        $this->patch(route('tasks.status', $task), ['status' => 'invalid'])->assertSessionHasErrors('status');
        $this->patch(route('tasks.status', $task), [])->assertSessionHasErrors('status');

        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'title' => 'Original task', 'status' => 'todo']);
    }

    public function test_searches_titles_case_insensitively_and_combines_filters(): void
    {
        Task::factory()->create(['title' => 'Design review', 'priority' => 'high', 'status' => 'in_progress']);
        Task::factory()->create(['title' => 'Design draft', 'priority' => 'low', 'status' => 'in_progress']);
        Task::factory()->create(['title' => 'Design handoff', 'priority' => 'high', 'status' => 'completed']);

        $this->get(route('tasks.index', ['search' => 'DESIGN', 'priority' => 'high', 'status' => 'in_progress']))
            ->assertOk()->assertSee('Design review')->assertDontSee('Design draft')->assertDontSee('Design handoff');
    }

    #[TestWith(['%'])]
    #[TestWith(['_'])]
    #[TestWith(["' OR 1=1 --"]) ]
    public function test_treats_search_characters_as_literal_text(string $search): void
    {
        Task::factory()->create(['title' => 'Match '.$search]);
        Task::factory()->create(['title' => 'Unrelated task']);

        $this->get(route('tasks.index', ['search' => $search]))->assertOk()->assertSee('Match '.$search)->assertDontSee('Unrelated task');
    }

    public function test_completed_page_only_lists_completed_tasks_even_with_conflicting_status_filter(): void
    {
        Task::factory()->create(['title' => 'Finished task', 'status' => 'completed']);
        Task::factory()->create(['title' => 'Active task', 'status' => 'todo']);

        $this->get(route('tasks.completed', ['status' => 'todo']))->assertOk()->assertSee('Finished task')->assertDontSee('Active task');
    }

    public function test_keeps_workspace_statistics_independent_of_filters(): void
    {
        Task::factory()->create(['status' => 'todo', 'title' => 'Planning']);
        Task::factory()->count(2)->create(['status' => 'in_progress']);
        Task::factory()->count(3)->create(['status' => 'completed']);

        $this->get(route('dashboard', ['search' => 'Planning']))->assertOk()->assertViewHas('counts', fn ($counts) => (int) $counts->total === 6 && (int) $counts->in_progress === 2 && (int) $counts->completed === 3);
    }

    public function test_orders_by_deadline_with_undated_tasks_last(): void
    {
        Task::factory()->create(['title' => 'No deadline', 'due_date' => null]);
        Task::factory()->create(['title' => 'Later deadline', 'due_date' => '2026-11-10']);
        Task::factory()->create(['title' => 'Earlier deadline', 'due_date' => '2026-11-03']);

        $this->get(route('tasks.index'))->assertOk()->assertSeeInOrder(['Earlier deadline', 'Later deadline', 'No deadline']);
    }

    public function test_marks_past_due_dates_but_not_completed_or_today_as_overdue(): void
    {
        $this->travelTo(now()->setDate(2026, 11, 2)->setTime(12, 0));
        $overdue = Task::factory()->create(['due_date' => '2026-11-01']);
        $today = Task::factory()->create(['due_date' => '2026-11-02']);
        $completed = Task::factory()->create(['due_date' => '2026-11-01', 'status' => 'completed']);
        $undated = Task::factory()->create(['due_date' => null]);

        $this->get(route('tasks.show', $overdue))->assertOk()->assertSee('Overdue');
        $this->get(route('tasks.show', $today))->assertOk()->assertDontSee('Overdue');
        $this->get(route('tasks.show', $completed))->assertOk()->assertDontSee('Overdue');
        $this->get(route('tasks.show', $undated))->assertOk()->assertDontSee('Overdue');
    }

    public function test_displays_empty_and_no_results_states(): void
    {
        $this->get(route('dashboard'))->assertOk()->assertSee('A fresh start, a clear mind.');
        $this->get(route('tasks.completed'))->assertOk()->assertSee('Your wins will live here');
        Task::factory()->create(['title' => 'Existing task']);

        $this->get(route('tasks.index', ['search' => 'no match']))->assertOk()->assertSee('No matching tasks');
    }

    public function test_paginates_tasks_and_preserves_filters(): void
    {
        Task::factory()->count(9)->create(['title' => 'Review', 'priority' => 'high']);

        $this->get(route('tasks.index', ['priority' => 'high', 'search' => 'Review']))->assertOk()
            ->assertViewHas('tasks', fn ($tasks) => $tasks->count() === 8 && $tasks->total() === 9)
            ->assertSee('priority=high&amp;search=Review&amp;page=2', false);
        $this->get(route('tasks.index', ['priority' => 'high', 'search' => 'Review', 'page' => 2]))->assertOk()
            ->assertViewHas('tasks', fn ($tasks) => $tasks->count() === 1);
    }

    public function test_escapes_titles_and_descriptions_in_all_views(): void
    {
        $task = Task::factory()->create(['title' => '<script>alert(1)</script>', 'description' => '<img src=x onerror=alert(2)>']);

        foreach (['tasks.index', 'tasks.show', 'tasks.edit'] as $route) {
            $this->get(route($route, $route === 'tasks.index' ? [] : $task))->assertOk()
                ->assertSee($task->title)->assertSee($task->description)
                ->assertDontSee($task->title, false)->assertDontSee($task->description, false);
        }
    }

    public function test_create_form_loads_and_missing_tasks_return_404(): void
    {
        $this->get(route('tasks.create'))->assertOk()->assertSee('Create task');
        $this->get(route('tasks.show', 999))->assertNotFound();
        $this->get(route('tasks.edit', 999))->assertNotFound();
        $this->delete(route('tasks.destroy', 999))->assertNotFound();
        $this->patch(route('tasks.status', 999), ['status' => 'completed'])->assertNotFound();
    }

    public function test_rejects_invalid_filters(): void
    {
        $this->from(route('tasks.index'))->get(route('tasks.index', ['priority' => 'invalid', 'status' => 'invalid']))
            ->assertRedirectToRoute('tasks.index')->assertSessionHasErrors(['priority', 'status']);
    }
}
