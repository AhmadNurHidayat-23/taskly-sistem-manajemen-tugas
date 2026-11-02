<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskRequest;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(array_keys(Task::STATUSES))],
            'priority' => ['nullable', Rule::in(array_keys(Task::PRIORITIES))],
        ]);
        $completedPage = $request->routeIs('tasks.completed');
        $tasks = Task::query()
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->whereRaw("title LIKE ? ESCAPE '\\'", ['%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $search).'%']))
            ->when($completedPage, fn ($query) => $query->where('status', 'completed'))
            ->when(! $completedPage && ($filters['status'] ?? null), fn ($query) => $query->where('status', $filters['status']))
            ->when($filters['priority'] ?? null, fn ($query, $priority) => $query->where('priority', $priority))
            ->orderByRaw('due_date IS NULL')->orderBy('due_date')->orderByDesc('id')
            ->paginate(8)->withQueryString();
        $counts = Task::query()->selectRaw("COUNT(*) AS total, COALESCE(SUM(status = 'in_progress'), 0) AS in_progress, COALESCE(SUM(status = 'completed'), 0) AS completed")->first();

        return view('tasks.index', compact('tasks', 'counts', 'filters', 'completedPage'));
    }

    public function create(): View
    {
        return view('tasks.form', ['task' => new Task(['priority' => 'medium', 'status' => 'todo'])]);
    }

    public function store(TaskRequest $request): RedirectResponse
    {
        $task = Task::create($request->validated());

        return to_route('tasks.show', $task)->with('success', 'Task created. A little more clarity for your day.');
    }

    public function show(Task $task): View
    {
        return view('tasks.show', compact('task'));
    }

    public function edit(Task $task): View
    {
        return view('tasks.form', compact('task'));
    }

    public function update(TaskRequest $request, Task $task): RedirectResponse
    {
        $task->update($request->validated());

        return to_route('tasks.show', $task)->with('success', 'Task updated successfully.');
    }

    public function status(Request $request, Task $task): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', Rule::in(array_keys(Task::STATUSES))]]);
        $task->update($data);

        return back()->with('success', 'Task marked as '.strtolower(Task::STATUSES[$task->status]).'.');
    }

    public function destroy(Task $task): RedirectResponse
    {
        $task->delete();

        return to_route('tasks.index')->with('success', 'Task deleted successfully.');
    }
}
