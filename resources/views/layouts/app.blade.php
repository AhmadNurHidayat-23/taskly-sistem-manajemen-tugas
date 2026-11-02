<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Taskly — a quiet workspace for your tasks. Stay focused. Get things done.">
    <title>@yield('title', 'My Workspace') — Taskly</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <a class="skip-link" href="#main">Skip to content</a>
    <aside class="sidebar" id="sidebar" aria-label="Main navigation">
        <a href="{{ route('dashboard') }}" class="brand" aria-label="Taskly home">taskly<span>.</span></a>
        <div class="workspace-label">PERSONAL WORKSPACE</div>
        <nav>
            <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}" @if(request()->routeIs('dashboard')) aria-current="page" @endif><x-icon name="dashboard" /> Dashboard</a>
            <a href="{{ route('tasks.index') }}" class="nav-item {{ request()->routeIs('tasks.*') && !request()->routeIs('tasks.completed') ? 'active' : '' }}" @if(request()->routeIs('tasks.index')) aria-current="page" @endif><x-icon name="tasks" /> All Tasks</a>
            <a href="{{ route('tasks.completed') }}" class="nav-item {{ request()->routeIs('tasks.completed') ? 'active' : '' }}" @if(request()->routeIs('tasks.completed')) aria-current="page" @endif><x-icon name="completed" /> Completed</a>
        </nav>
        <div class="sidebar-bottom"><span class="workspace-avatar">P</span><div><strong>Personal workspace</strong><span>Just you. Your next big thing.</span></div></div>
    </aside>
    <div class="app-shell">
        <header class="topbar">
            <button type="button" class="icon-button mobile-menu" aria-label="Toggle navigation" aria-controls="sidebar" aria-expanded="false" data-menu><x-icon name="menu" /></button>
            <div class="breadcrumb">Workspace <x-icon name="arrow" size="13" /><span>@yield('breadcrumb', 'Overview')</span></div>
            <div class="topbar-date"><x-icon name="calendar" size="16" />{{ now()->format('l, d M Y') }}</div>
        </header>
        <main id="main" tabindex="-1">
            @if(session('success'))
                <div class="notice" role="status"><x-icon name="completed" size="18" /><span>{{ session('success') }}</span><button type="button" class="icon-button" aria-label="Dismiss notification" data-dismiss><x-icon name="close" size="16" /></button></div>
            @endif
            @yield('content')
            <footer class="page-footer"><span>A little structure. A lot of possibility.</span><span>taskly. / Your space to focus</span></footer>
        </main>
    </div>
    <dialog id="delete-dialog" class="confirm-dialog" aria-labelledby="delete-title">
        <div class="dialog-icon"><x-icon name="trash" size="24" /></div>
        <h2 id="delete-title">Delete this task?</h2>
        <p>This will permanently delete <strong data-delete-title>this task</strong>. This action cannot be undone.</p>
        <div class="dialog-actions"><button type="button" class="button secondary" data-cancel-delete>Keep task</button><button type="button" class="button danger" data-confirm-delete>Delete task</button></div>
    </dialog>
</body>
</html>
