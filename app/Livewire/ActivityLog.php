<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ActivityLog as ActivityLogModel;

class ActivityLog extends Component
{
    use WithPagination;

    public string $search       = '';
    public string $filterType   = '';
    public string $filterStatus = '';
    public string $filterDate   = '';
    public int    $perPage      = 15;

    public function updatingSearch(): void       { $this->resetPage(); }
    public function updatingFilterType(): void   { $this->resetPage(); }
    public function updatingFilterStatus(): void { $this->resetPage(); }
    public function updatingFilterDate(): void   { $this->resetPage(); }
    public function updatingPerPage(): void      { $this->resetPage(); }

    public function resetFilters(): void
    {
        $this->reset(['search', 'filterType', 'filterStatus', 'filterDate']);
        $this->resetPage();
    }

    public function render()
    {
        $query = ActivityLogModel::query()
            ->when($this->search, fn($q) => $q->where(function ($q) {
                $q->where('event', 'like', "%{$this->search}%")
                    ->orWhere('message', 'like', "%{$this->search}%");
            }))
            ->when($this->filterType,   fn($q) => $q->where('type', $this->filterType))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterDate,   fn($q) => $q->whereDate('created_at', $this->filterDate));

        $logs  = $query->clone()->orderByDesc('created_at')->paginate($this->perPage);
        $stats = $query->clone()->selectRaw("COUNT(*) as total, SUM(status='success') as success, SUM(status='error') as error")->first();
        $types = ActivityLogModel::select('type')->distinct()->orderBy('type')->pluck('type');

        return view('livewire.activity-log', compact('logs', 'stats', 'types'))
            ->layout('components.layouts.console-box', ['title' => 'Activity Log']);
    }
}
