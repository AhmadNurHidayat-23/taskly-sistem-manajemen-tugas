@extends('layouts.app')
@section('title', $completedPage ? 'Completed' : (request()->routeIs('dashboard') ? 'My Workspace' : 'All Tasks'))
@section('breadcrumb', $completedPage ? 'Completed' : (request()->routeIs('dashboard') ? 'Overview' : 'All Tasks'))
@section('content')
    <div class="page-heading">
        <div><div class="eyebrow">MAKE ROOM FOR WHAT MATTERS</div><h1>{{ $completedPage ? 'Completed tasks' : (request()->routeIs('dashboard') ? 'My Workspace' : 'All tasks') }}<span class="heading-dot">.</span></h1><p>{{ $completedPage ? 'Good work. Take a moment to see how far you’ve come.' : 'Stay focused. Get things done.' }}</p></div>
        <a href="{{ route('tasks.create') }}" class="button primary"><x-icon name="plus" size="18" /> New Task</a>
    </div>
    <section class="stats-grid" aria-label="Workspace statistics">
        <a class="stat-card" href="{{ route('tasks.index') }}"><div class="stat-label">Total Tasks <x-icon name="tasks" size="19" /></div><div class="stat-number">{{ str_pad($counts->total, 2, '0', STR_PAD_LEFT) }}</div><div class="stat-caption">Everything in your workspace <x-icon name="arrow" size="14" /></div></a>
        <a class="stat-card" href="{{ route('tasks.index', ['status' => 'in_progress']) }}"><div class="stat-label">In Progress <x-icon name="clock" size="19" /></div><div class="stat-number">{{ str_pad($counts->in_progress, 2, '0', STR_PAD_LEFT) }}<span class="stat-marker progress-marker"></span></div><div class="stat-caption">One step closer, every day <x-icon name="arrow" size="14" /></div></a>
        <a class="stat-card" href="{{ route('tasks.completed') }}"><div class="stat-label">Completed <x-icon name="completed" size="19" /></div><div class="stat-number">{{ str_pad($counts->completed, 2, '0', STR_PAD_LEFT) }}<span class="stat-marker completed-marker"></span></div><div class="stat-caption">Off your list. On to the next. <x-icon name="arrow" size="14" /></div></a>
    </section>
    <section class="task-section" aria-labelledby="tasks-heading">
        <div class="section-heading"><div class="section-title"><h2 id="tasks-heading">{{ $completedPage ? 'Finished & filed' : 'Your Tasks' }}</h2><span class="count-badge">{{ $tasks->total() }}</span></div><span class="sort-note"><x-icon name="calendar" size="14" /> By due date</span></div>
        <form method="GET" action="{{ $completedPage ? route('tasks.completed') : url()->current() }}" class="filter-bar" role="search">
            <div class="search-field"><x-icon name="search" size="18" /><input type="search" name="search" aria-label="Search tasks by title" placeholder="Search your tasks…" value="{{ $filters['search'] ?? '' }}" maxlength="255"></div>
            <div class="filter-controls">
                @unless($completedPage)
                    <label class="sr-only" for="filter-status">Filter by status</label><select name="status" id="filter-status"><option value="">All statuses</option>@foreach(\App\Models\Task::STATUSES as $value => $label)<option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>@endforeach</select>
                @endunless
                <label class="sr-only" for="filter-priority">Filter by priority</label><select name="priority" id="filter-priority"><option value="">All priorities</option>@foreach(\App\Models\Task::PRIORITIES as $value => $label)<option value="{{ $value }}" @selected(($filters['priority'] ?? '') === $value)>{{ $label }}</option>@endforeach</select>
                <button class="button secondary filter-submit" type="submit">Apply</button>
                @if(array_filter($filters))<a class="clear-filter" href="{{ url()->current() }}">Reset</a>@endif
            </div>
        </form>
        @if($errors->any())<div class="error-summary" role="alert">{{ $errors->first() }} <a href="{{ url()->current() }}">Reset filters</a></div>@endif
        <div class="task-list">
            <div class="list-header"><span>Task name</span><span>Due date</span><span>Priority</span><span>Status</span><span></span></div>
            @forelse($tasks as $task)
                <x-task-row :task="$task" />
            @empty
                <div class="empty-state"><div class="empty-icon"><x-icon name="{{ $completedPage ? 'completed' : 'tasks' }}" size="28" /></div><h3>{{ array_filter($filters) ? 'No matching tasks' : ($completedPage ? 'Your wins will live here' : 'A fresh start, a clear mind.') }}</h3><p>{{ array_filter($filters) ? 'Try another search or adjust your filters.' : ($completedPage ? 'Complete your first task to see it here.' : 'Add your first task and make space for what’s next.') }}</p><a class="button {{ array_filter($filters) || $completedPage ? 'secondary' : 'primary' }}" href="{{ array_filter($filters) ? url()->current() : ($completedPage ? route('tasks.index') : route('tasks.create')) }}">{{ array_filter($filters) ? 'Clear filters' : ($completedPage ? 'Explore your tasks' : 'Create your first task') }}<x-icon name="arrow" size="16" /></a></div>
            @endforelse
        </div>
        @if($tasks->hasPages())<div class="pagination"><span>Showing {{ $tasks->firstItem() }}–{{ $tasks->lastItem() }} of {{ $tasks->total() }}</span><div>@if($tasks->onFirstPage())<span class="button secondary disabled">Previous</span>@else<a class="button secondary" href="{{ $tasks->previousPageUrl() }}">Previous</a>@endif<span>{{ $tasks->currentPage() }} / {{ $tasks->lastPage() }}</span>@if($tasks->hasMorePages())<a class="button secondary" href="{{ $tasks->nextPageUrl() }}">Next</a>@else<span class="button secondary disabled">Next</span>@endif</div></div>@endif
    </section>
@endsection
