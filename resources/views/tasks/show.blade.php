@extends('layouts.app')
@section('title', $task->title)
@section('breadcrumb', 'Task details')
@section('content')
    <a href="{{ route('tasks.index') }}" class="back-link"><x-icon name="back" size="16" />Back to all tasks</a>
    <div class="detail-heading"><div><div class="eyebrow">TASK DETAILS / {{ str_pad($task->id, 3, '0', STR_PAD_LEFT) }}</div><h1 class="detail-title">{{ $task->title }}</h1></div><a class="button secondary" href="{{ route('tasks.edit', $task) }}"><x-icon name="edit" size="16" /> Edit task</a></div>
    @if($errors->any())<div class="error-summary" role="alert">{{ $errors->first() }}</div>@endif
    <article class="detail-card">
        <dl class="detail-meta">
            <div><dt><x-icon name="clock" size="16" /> Status</dt><dd><span class="status status-{{ $task->status }}"><span></span>{{ \App\Models\Task::STATUSES[$task->status] }}</span></dd></div>
            <div><dt><x-icon name="flag" size="16" /> Priority</dt><dd><span class="priority priority-{{ $task->priority }}"><span class="priority-bars" aria-hidden="true"><i></i><i></i><i></i></span>{{ \App\Models\Task::PRIORITIES[$task->priority] }}</span></dd></div>
            <div><dt><x-icon name="calendar" size="16" /> Due date</dt><dd class="{{ $task->isOverdue() ? 'overdue' : '' }}">{{ $task->due_date?->format('d M Y') ?? 'No due date' }} @if($task->isOverdue())<span class="overdue-label">Overdue</span>@endif</dd></div>
        </dl>
        <div class="detail-description"><h2>Description</h2><p class="preserve-lines {{ $task->description ? '' : 'muted' }}">{{ $task->description ?: 'No description added. You can add one when you edit this task.' }}</p></div>
        <div class="detail-timestamps"><span>Created {{ $task->created_at->format('d M Y, H:i') }}</span><span>Updated {{ $task->updated_at->format('d M Y, H:i') }}</span></div>
        <div class="detail-actions">
            <form method="POST" action="{{ route('tasks.status', $task) }}" class="status-form">@csrf @method('PATCH')<label class="sr-only" for="task-status">Change status</label><select name="status" id="task-status">@foreach(\App\Models\Task::STATUSES as $value => $label)<option value="{{ $value }}" @selected($task->status === $value)>{{ $label }}</option>@endforeach</select><button class="button secondary" type="submit">Update status</button></form>
            @unless($task->status === 'completed')<form method="POST" action="{{ route('tasks.status', $task) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="completed"><button type="submit" class="button primary"><x-icon name="check" size="17" />Mark complete</button></form>@endunless
        </div>
    </article>
    <form method="POST" action="{{ route('tasks.destroy', $task) }}" class="delete-form" data-delete-form data-task-title="{{ $task->title }}">@csrf @method('DELETE')<button type="submit" class="delete-button"><x-icon name="trash" size="16" />Delete task</button></form>
@endsection
