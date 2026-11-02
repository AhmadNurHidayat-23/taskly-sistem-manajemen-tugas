@props(['task'])
<article class="task-row {{ $task->status === 'completed' ? 'is-completed' : '' }}">
    <div class="task-name-cell">
        <form method="POST" action="{{ route('tasks.status', $task) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="{{ $task->status === 'completed' ? 'todo' : 'completed' }}"><button type="submit" class="completion-button {{ $task->status === 'completed' ? 'is-checked' : '' }}" aria-label="{{ $task->status === 'completed' ? 'Reopen' : 'Complete' }} {{ $task->title }}">@if($task->status === 'completed')<x-icon name="check" size="13" />@endif</button></form>
        <div class="task-copy"><a class="task-title" href="{{ route('tasks.show', $task) }}">{{ $task->title }}</a><span class="task-description">{{ $task->description ?: 'No description added' }}</span></div>
    </div>
    <div class="due-date {{ $task->isOverdue() ? 'overdue' : '' }}"><x-icon name="calendar" size="14" />@if($task->due_date)<time datetime="{{ $task->due_date->toDateString() }}">{{ $task->due_date->isToday() ? 'Today' : $task->due_date->format('d M Y') }}</time>@else<span>No due date</span>@endif @if($task->isOverdue())<span class="overdue-label">Overdue</span>@endif</div>
    <span class="priority priority-{{ $task->priority }}"><span class="priority-bars" aria-hidden="true"><i></i><i></i><i></i></span>{{ \App\Models\Task::PRIORITIES[$task->priority] }}</span>
    <span class="status status-{{ $task->status }}"><span></span>{{ \App\Models\Task::STATUSES[$task->status] }}</span>
    <a href="{{ route('tasks.show', $task) }}" class="icon-button row-arrow" aria-label="View {{ $task->title }}"><x-icon name="arrow" size="17" /></a>
</article>
