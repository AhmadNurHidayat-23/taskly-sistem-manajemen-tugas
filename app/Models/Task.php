<?php

namespace App\Models;

use Database\Factories\TaskFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    /** @use HasFactory<TaskFactory> */
    use HasFactory;

    public const PRIORITIES = ['low' => 'Low', 'medium' => 'Medium', 'high' => 'High'];

    public const STATUSES = ['todo' => 'To Do', 'in_progress' => 'In Progress', 'completed' => 'Completed'];

    protected $fillable = ['title', 'description', 'due_date', 'priority', 'status'];

    protected function casts(): array
    {
        return ['due_date' => 'date'];
    }

    public function isOverdue(): bool
    {
        return $this->status !== 'completed' && $this->due_date !== null && $this->due_date->isBefore(today());
    }
}
