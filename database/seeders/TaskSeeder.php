<?php

namespace Database\Seeders;

use App\Models\Task;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $tasks = [
            ['Review project documentation', 'Read through the project brief and collect feedback before the next review.', 0, 'high', 'in_progress'],
            ['Deliver files to client', 'Package the final deliverables and share the handoff notes.', 0, 'high', 'todo'],
            ['Design changes for productivity app', 'Refine the task list spacing and review the mobile experience.', 2, 'medium', 'in_progress'],
            ['Plan next week’s priorities', 'Make room for the three things that matter most.', 4, 'low', 'todo'],
            ['Review dashboard wireframes', 'Review the first round of workspace wireframes.', -1, 'medium', 'todo'],
            ['Set up the project workspace', 'Everything is in place. Ready for a fresh start.', -2, 'low', 'completed'],
        ];

        foreach ($tasks as [$title, $description, $days, $priority, $status]) {
            Task::firstOrCreate(['title' => $title], compact('description', 'priority', 'status') + ['due_date' => today()->addDays($days)]);
        }
    }
}
