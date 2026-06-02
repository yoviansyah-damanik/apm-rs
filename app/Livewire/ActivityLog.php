<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ActivityLog as ActivityLogModel;

class ActivityLog extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterType = '';
    public string $filterStatus = '';
    public string $filterDate = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterType(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatingFilterDate(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $logs = ActivityLogModel::query()
            ->when($this->search, fn($q) => $q->where(function ($q) {
                $q->where('event', 'like', "%{$this->search}%")
                    ->orWhere('message', 'like', "%{$this->search}%");
            }))
            ->when($this->filterType, fn($q) => $q->where('type', $this->filterType))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterDate, fn($q) => $q->whereDate('created_at', $this->filterDate))
            ->orderByDesc('created_at')
            ->paginate(10);

        $types = ActivityLogModel::select('type')->distinct()->pluck('type');

        return view('livewire.activity-log', compact('logs', 'types'))
            ->layout('components.layouts.console-box', ['title' => 'Activity Log']);
    }
}
