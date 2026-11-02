@extends('layouts.app')
@section('title', $task->exists ? 'Edit Task' : 'New Task')
@section('breadcrumb', $task->exists ? 'Edit task' : 'New task')
@section('content')
    <a class="back-link" href="{{ $task->exists ? route('tasks.show', $task) : route('tasks.index') }}"><x-icon name="back" size="16" />{{ $task->exists ? 'Back to task' : 'Back to all tasks' }}</a>
    <div class="page-heading compact"><div><div class="eyebrow">ONE THING AT A TIME</div><h1>{{ $task->exists ? 'Edit task' : 'A new task' }}<span class="heading-dot">.</span></h1><p>{{ $task->exists ? 'A little adjustment. A clearer path forward.' : 'Turn an idea into your next step.' }}</p></div></div>
    <form class="form-card" method="POST" action="{{ $task->exists ? route('tasks.update', $task) : route('tasks.store') }}">
        @csrf
        @if($task->exists) @method('PUT') @endif
        @if($errors->any())<div class="error-summary" role="alert"><strong>Please check the details below.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <div class="field"><label for="title">Task title <span class="required">*</span></label><input id="title" name="title" type="text" value="{{ old('title', $task->title) }}" placeholder="What would you like to get done?" required maxlength="255" autofocus @error('title') aria-invalid="true" aria-describedby="title-error" @enderror>@error('title')<span class="field-error" id="title-error">{{ $message }}</span>@enderror</div>
        <div class="field"><label for="description">Description <span class="optional">Optional</span></label><textarea id="description" name="description" rows="5" maxlength="10000" placeholder="Add a little context, notes, or a plan of action…" @error('description') aria-invalid="true" aria-describedby="description-error" @enderror>{{ old('description', $task->description) }}</textarea>@error('description')<span class="field-error" id="description-error">{{ $message }}</span>@enderror</div>
        <div class="form-grid">
            <div class="field"><label for="due_date">Due date <span class="optional">Optional</span></label><input id="due_date" name="due_date" type="date" value="{{ old('due_date', $task->due_date?->format('Y-m-d')) }}" @error('due_date') aria-invalid="true" aria-describedby="due-date-error" @enderror>@error('due_date')<span class="field-error" id="due-date-error">{{ $message }}</span>@enderror</div>
            <div class="field"><label for="priority">Priority</label><select name="priority" id="priority" required @error('priority') aria-invalid="true" aria-describedby="priority-error" @enderror>@foreach(\App\Models\Task::PRIORITIES as $value => $label)<option value="{{ $value }}" @selected(old('priority', $task->priority) === $value)>{{ $label }}</option>@endforeach</select>@error('priority')<span class="field-error" id="priority-error">{{ $message }}</span>@enderror</div>
            <div class="field"><label for="status">Status</label><select name="status" id="status" required @error('status') aria-invalid="true" aria-describedby="status-error" @enderror>@foreach(\App\Models\Task::STATUSES as $value => $label)<option value="{{ $value }}" @selected(old('status', $task->status) === $value)>{{ $label }}</option>@endforeach</select>@error('status')<span class="field-error" id="status-error">{{ $message }}</span>@enderror</div>
        </div>
        <div class="form-actions"><span class="form-hint">Small steps add up.</span><a class="button secondary" href="{{ $task->exists ? route('tasks.show', $task) : route('tasks.index') }}">Cancel</a><button type="submit" class="button primary"><x-icon name="{{ $task->exists ? 'check' : 'plus' }}" size="17" />{{ $task->exists ? 'Save changes' : 'Create task' }}</button></div>
    </form>
@endsection
