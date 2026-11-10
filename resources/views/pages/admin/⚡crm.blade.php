<?php

use App\Enums\InquiryStatus;
use App\Enums\InquiryType;
use App\Enums\TaskStatus;
use App\Models\Car;
use App\Models\Inquiry;
use App\Models\Task;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts::admin')] class extends Component
{
    use WithPagination;

    #[Url(except: 'dash')]
    public string $screen = 'dash';

    #[Url(except: '')]
    public string $type = '';

    #[Url(except: '', as: 'status')]
    public string $leadStatus = '';

    #[Url(except: '', as: 'q')]
    public string $search = '';

    #[Url(except: 'new')]
    public string $sort = 'new';

    #[Url(except: 'open', as: 'tasks')]
    public string $taskFilter = 'open';

    public ?int $openId = null;

    public ?int $day = null;

    public string $taskTitle = '';

    public string $taskNotes = '';

    public string $taskDue = '';

    public string $taskInquiryId = '';

    public string $notice = '';

    public function boot(): void
    {
        abort_unless(auth()->check(), 403);
    }

    public function rendering($view): void
    {
        if (! in_array($this->screen, ['dash', 'leads', 'pipe', 'tasks', 'cal', 'reports', 'team', 'stock', 'ann'], true)) {
            $this->screen = 'dash';
        }

        $view->layout('layouts::admin', [
            'title' => 'CRM',
        ]);
    }

    public function show(string $screen): void
    {
        if (! in_array($screen, ['dash', 'leads', 'pipe', 'tasks', 'cal', 'reports', 'team', 'stock', 'ann'], true)) {
            return;
        }

        $this->screen = $screen;
        $this->notice = '';
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedType(): void
    {
        $this->resetPage();
    }

    public function updatedLeadStatus(): void
    {
        $this->resetPage();
    }

    public function updatedSort(): void
    {
        $this->resetPage();
    }

    public function updateStatus(int $inquiryId, string $status): void
    {
        $case = InquiryStatus::tryFrom($status);

        if ($case === null) {
            return;
        }

        $updated = Inquiry::query()->whereKey($inquiryId)->update(['status' => $case]);

        if ($updated > 0) {
            $this->notice = 'Lead status saved.';
        }

        $this->refreshLists();
    }

    public function open(int $inquiryId): void
    {
        $this->openId = Inquiry::query()->whereKey($inquiryId)->exists() ? $inquiryId : null;
        unset($this->openInquiry);
    }

    public function closeLead(): void
    {
        $this->openId = null;
    }

    public function saveTask(): void
    {
        $validated = $this->validate([
            'taskTitle' => ['required', 'string', 'max:150'],
            'taskNotes' => ['nullable', 'string', 'max:2000'],
            'taskDue' => ['exclude_if:taskDue,', 'date'],
            'taskInquiryId' => ['exclude_if:taskInquiryId,', 'integer', 'exists:inquiries,id'],
        ]);

        $userId = (int) auth()->id();

        Task::query()->create([
            'title' => $validated['taskTitle'],
            'notes' => ($validated['taskNotes'] ?? '') !== '' ? $validated['taskNotes'] : null,
            'due_on' => $validated['taskDue'] ?? null,
            'status' => TaskStatus::Open,
            'inquiry_id' => isset($validated['taskInquiryId']) ? (int) $validated['taskInquiryId'] : null,
            'created_by' => $userId,
            'assigned_to' => $userId,
        ]);

        $this->reset('taskTitle', 'taskNotes', 'taskDue', 'taskInquiryId');
        $this->notice = 'Task added.';
        $this->refreshLists();
    }

    public function completeTask(int $taskId): void
    {
        $updated = Task::query()
            ->whereKey($taskId)
            ->where('status', TaskStatus::Open)
            ->update(['status' => TaskStatus::Done]);

        if ($updated > 0) {
            $this->notice = 'Task marked done.';
        }

        $this->refreshLists();
    }

    /**
     * @return Collection<int, Inquiry>
     */
    #[Computed]
    public function inquiries(): Collection
    {
        return Inquiry::query()->with('car.brand')->latest()->get();
    }

    /**
     * @return LengthAwarePaginator<int, Inquiry>
     */
    #[Computed]
    public function leadPage(): LengthAwarePaginator
    {
        $type = InquiryType::tryFrom($this->type);
        $status = InquiryStatus::tryFrom($this->leadStatus);
        $term = trim($this->search);

        $leads = Inquiry::query()->with('car.brand');

        if ($type instanceof InquiryType) {
            $leads->where('type', $type);
        }

        if ($status instanceof InquiryStatus) {
            $leads->where('status', $status);
        }

        if ($term !== '') {
            $like = '%'.addcslashes($term, '%_\\').'%';
            $leads->where(function ($query) use ($like): void {
                $query->where('name', 'like', $like)->orWhere('phone', 'like', $like);
            });
        }

        match ($this->sort) {
            'old' => $leads->oldest(),
            'name' => $leads->orderBy('name'),
            default => $leads->latest(),
        };

        return $leads->paginate(12);
    }

    /**
     * @return array{leads: int, new: int, open: int, done: int, overdue: int}
     */
    #[Computed]
    public function stats(): array
    {
        return [
            'leads' => Inquiry::query()->count(),
            'new' => Inquiry::query()->where('status', InquiryStatus::New)->count(),
            'open' => Task::query()->where('status', TaskStatus::Open)->count(),
            'done' => Task::query()->where('status', TaskStatus::Done)->count(),
            'overdue' => Task::query()->where('status', TaskStatus::Open)->whereDate('due_on', '<', today())->count(),
        ];
    }

    /**
     * @return array<string, int>
     */
    #[Computed]
    public function statusCounts(): array
    {
        $raw = Inquiry::query()
            ->toBase()
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $counts = [];

        foreach (InquiryStatus::cases() as $case) {
            $counts[$case->value] = (int) ($raw[$case->value] ?? 0);
        }

        return $counts;
    }

    /**
     * @return array<string, int>
     */
    #[Computed]
    public function typeCounts(): array
    {
        $raw = Inquiry::query()
            ->toBase()
            ->selectRaw('type, count(*) as aggregate')
            ->groupBy('type')
            ->pluck('aggregate', 'type');

        $counts = [];

        foreach (InquiryType::cases() as $case) {
            $counts[$case->value] = (int) ($raw[$case->value] ?? 0);
        }

        return $counts;
    }

    /**
     * @return Collection<int, Car>
     */
    #[Computed]
    public function cars(): Collection
    {
        return Car::query()->with(['brand', 'galleryImages'])->orderBy('name')->get();
    }

    /**
     * @return Collection<int, Task>
     */
    #[Computed]
    public function tasks(): Collection
    {
        return Task::query()->with('inquiry')->latest()->get();
    }

    /**
     * @return Collection<int, Task>
     */
    #[Computed]
    public function visibleTasks(): Collection
    {
        return $this->tasks->filter(function (Task $task): bool {
            return match ($this->taskFilter) {
                'done' => $task->status === TaskStatus::Done,
                'overdue' => $this->isOverdue($task),
                default => $task->status === TaskStatus::Open,
            };
        })->values();
    }

    /**
     * @return Collection<int, Inquiry>
     */
    #[Computed]
    public function recentLeads(): Collection
    {
        return Inquiry::query()->with('car')->latest()->limit(5)->get();
    }

    /**
     * @return Collection<int, Task>
     */
    #[Computed]
    public function upcomingTasks(): Collection
    {
        return Task::query()
            ->with('inquiry')
            ->where('status', TaskStatus::Open)
            ->whereDate('due_on', '>=', today())
            ->orderBy('due_on')
            ->limit(5)
            ->get();
    }

    /**
     * @return Collection<int, Task>
     */
    #[Computed]
    public function overdueTasks(): Collection
    {
        return Task::query()
            ->with('inquiry')
            ->where('status', TaskStatus::Open)
            ->whereDate('due_on', '<', today())
            ->orderBy('due_on')
            ->limit(5)
            ->get();
    }

    #[Computed]
    public function openInquiry(): ?Inquiry
    {
        if ($this->openId === null) {
            return null;
        }

        return Inquiry::query()->with(['car.brand', 'tasks'])->find($this->openId);
    }

    /**
     * @return array{blank: int, days: int, today: int|null}
     */
    #[Computed]
    public function calendar(): array
    {
        $start = Carbon::create(2026, 11, 1);

        return [
            'blank' => $start->dayOfWeek,
            'days' => $start->daysInMonth,
            'today' => now()->format('Y-m') === '2026-11' ? now()->day : null,
        ];
    }

    private function isOverdue(Task $task): bool
    {
        return $task->status === TaskStatus::Open
            && $task->due_on !== null
            && $task->due_on->toDateString() < today()->toDateString();
    }

    private function refreshLists(): void
    {
        unset(
            $this->inquiries,
            $this->leadPage,
            $this->stats,
            $this->statusCounts,
            $this->typeCounts,
            $this->tasks,
            $this->visibleTasks,
            $this->recentLeads,
            $this->upcomingTasks,
            $this->overdueTasks,
            $this->openInquiry,
        );
    }
};
?>

<div class="lg:grid lg:grid-cols-[15rem_1fr]">
    <aside class="sticky top-0 hidden max-h-screen flex-col gap-4 overflow-y-auto border-r border-line bg-panel p-4 lg:flex">
        <p class="px-3 font-display text-xl text-cream">CRM</p>
        @include('partials.admin.crm-nav')
    </aside>

    <div class="flex min-w-0 flex-col gap-5 px-5 py-6 sm:px-8">
        <details class="border-b border-line pb-3 lg:hidden">
            <summary class="cursor-pointer list-none text-sm tracking-[0.14em] text-cream uppercase [&::-webkit-details-marker]:hidden">Menu</summary>
            <div class="mt-3">
                @include('partials.admin.crm-nav')
            </div>
        </details>

        @if ($notice !== '')
            <p class="rounded-lg border border-gold/40 bg-gold/10 px-3 py-2 text-sm text-gold">{{ $notice }}</p>
        @endif

        @if ($screen === 'dash')
            <div>
                <h1 class="font-display text-2xl text-cream">Overview</h1>
                <p class="mt-1 text-sm text-cream-dim">{{ now()->format('l, j F Y') }}</p>
            </div>
            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
                @foreach ([
                    'Leads' => $this->stats['leads'],
                    'New' => $this->stats['new'],
                    'Open tasks' => $this->stats['open'],
                    'Done' => $this->stats['done'],
                    'Overdue' => $this->stats['overdue'],
                ] as $label => $value)
                    <div class="rounded-xl border border-line bg-panel px-3 py-3">
                        <p class="text-[11px] tracking-[0.14em] text-cream-dim uppercase">{{ $label }}</p>
                        <p class="mt-1 font-display text-2xl text-cream">{{ $value }}</p>
                    </div>
                @endforeach
            </div>
            <div class="grid gap-3 lg:grid-cols-2">
                <section class="rounded-xl border border-line bg-panel p-4">
                    <div class="mb-3 flex items-center justify-between gap-3">
                        <h2 class="text-sm text-cream">Pipeline</h2>
                        <button type="button" wire:click="show('pipe')" class="text-[11px] tracking-[0.14em] text-gold uppercase">Open</button>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach (\App\Enums\InquiryStatus::cases() as $status)
                            <div class="rounded-lg border border-line px-3 py-2">
                                <p class="text-[11px] tracking-[0.12em] text-cream-dim uppercase">{{ $status->label() }}</p>
                                <p class="font-display text-xl text-cream">{{ $this->statusCounts[$status->value] }}</p>
                            </div>
                        @endforeach
                    </div>
                </section>
                <section class="rounded-xl border border-line bg-panel p-4">
                    <div class="mb-3 flex items-center justify-between gap-3">
                        <h2 class="text-sm text-cream">Recent leads</h2>
                        <button type="button" wire:click="show('leads')" class="text-[11px] tracking-[0.14em] text-gold uppercase">View all</button>
                    </div>
                    <div class="flex flex-col">
                        @forelse ($this->recentLeads as $inquiry)
                            <button type="button" wire:click="open({{ $inquiry->id }})" class="flex items-center justify-between gap-3 border-t border-line py-2 text-left first:border-t-0">
                                <span>
                                    <span class="block text-sm text-cream">{{ $inquiry->name }}</span>
                                    <span class="text-xs text-cream-dim">{{ $inquiry->car?->name ?? 'No car attached' }}</span>
                                </span>
                                <span class="text-[10px] tracking-[0.12em] text-gold uppercase">{{ $inquiry->status->label() }}</span>
                            </button>
                        @empty
                            <p class="text-sm text-cream-dim">No leads yet.</p>
                        @endforelse
                    </div>
                </section>
                <section class="rounded-xl border border-line bg-panel p-4">
                    <div class="mb-3 flex items-center justify-between gap-3">
                        <h2 class="text-sm text-cream">Upcoming tasks</h2>
                        <button type="button" wire:click="show('tasks')" class="text-[11px] tracking-[0.14em] text-gold uppercase">My tasks</button>
                    </div>
                    @forelse ($this->upcomingTasks as $task)
                        <div class="border-t border-line py-2 first:border-t-0">
                            <p class="text-sm text-cream">{{ $task->title }}</p>
                            <p class="text-xs text-cream-dim">{{ $task->due_on?->format('j M Y') }}@if ($task->inquiry) · {{ $task->inquiry->name }}@endif</p>
                        </div>
                    @empty
                        <p class="text-sm text-cream-dim">No upcoming tasks.</p>
                    @endforelse
                </section>
                <section class="rounded-xl border border-line bg-panel p-4">
                    <h2 class="mb-3 text-sm text-cream">Overdue tasks</h2>
                    @forelse ($this->overdueTasks as $task)
                        <div class="border-t border-line py-2 first:border-t-0">
                            <p class="text-sm text-cream">{{ $task->title }}</p>
                            <p class="text-xs text-cream-dim">{{ $task->due_on?->format('j M Y') }}@if ($task->inquiry) · {{ $task->inquiry->name }}@endif</p>
                        </div>
                    @empty
                        <p class="text-sm text-cream-dim">No overdue tasks.</p>
                    @endforelse
                </section>
            </div>
            <details class="rounded-xl border border-line px-4 py-3 text-sm text-cream-dim">
                <summary class="cursor-pointer text-cream">How a lead arrives</summary>
                <p class="mt-2">A visitor sends a car note, a contact note, a sell note, or a test-drive request. The note is saved with the name, phone, and car when one was chosen. It shows on this desk. Nobody is assigned yet.</p>
            </details>
        @elseif ($screen === 'leads')
            <div class="flex flex-wrap items-end justify-between gap-3">
                <h1 class="font-display text-2xl text-cream">Leads</h1>
                <p class="text-sm text-cream-dim">{{ $this->leadPage->total() }} saved</p>
            </div>
            <div class="grid gap-3 md:grid-cols-4">
                <label class="text-[11px] tracking-[0.14em] text-cream-dim uppercase md:col-span-2">
                    Search
                    <input wire:model.live.debounce.300ms="search" type="search" placeholder="Name or phone" class="mt-1 w-full border border-line bg-ink px-3 py-2 text-sm tracking-normal text-cream normal-case placeholder:text-cream-dim">
                </label>
                <label class="text-[11px] tracking-[0.14em] text-cream-dim uppercase">
                    Source
                    <select wire:model.live="type" class="mt-1 w-full border border-line bg-ink px-3 py-2 text-sm tracking-normal text-cream normal-case">
                        <option value="">All</option>
                        @foreach (\App\Enums\InquiryType::cases() as $option)
                            <option value="{{ $option->value }}">{{ $option->label() }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="text-[11px] tracking-[0.14em] text-cream-dim uppercase">
                    Status
                    <select wire:model.live="leadStatus" class="mt-1 w-full border border-line bg-ink px-3 py-2 text-sm tracking-normal text-cream normal-case">
                        <option value="">All</option>
                        @foreach (\App\Enums\InquiryStatus::cases() as $option)
                            <option value="{{ $option->value }}">{{ $option->label() }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="text-[11px] tracking-[0.14em] text-cream-dim uppercase">
                    Sort
                    <select wire:model.live="sort" class="mt-1 w-full border border-line bg-ink px-3 py-2 text-sm tracking-normal text-cream normal-case">
                        <option value="new">Newest</option>
                        <option value="old">Oldest</option>
                        <option value="name">Name</option>
                    </select>
                </label>
            </div>
            <div class="rounded-xl border border-line bg-panel p-3">
                @include('partials.admin.crm-leads', ['leads' => $this->leadPage])
                @if ($this->leadPage->hasPages())
                    <div class="mt-3 flex items-center justify-between gap-3 border-t border-line pt-3 text-sm">
                        <p class="text-cream-dim">{{ $this->leadPage->firstItem() }}–{{ $this->leadPage->lastItem() }} of {{ $this->leadPage->total() }}</p>
                        <div class="flex gap-2">
                            <button type="button" wire:click="previousPage" @disabled($this->leadPage->onFirstPage()) class="rounded-full border border-line px-3 py-1.5 text-[11px] tracking-[0.14em] text-cream uppercase disabled:text-cream-dim">Previous</button>
                            <button type="button" wire:click="nextPage" @disabled(! $this->leadPage->hasMorePages()) class="rounded-full border border-line px-3 py-1.5 text-[11px] tracking-[0.14em] text-cream uppercase disabled:text-cream-dim">Next</button>
                        </div>
                    </div>
                @endif
            </div>
        @elseif ($screen === 'pipe')
            <div>
                <h1 class="font-display text-2xl text-cream">Sales Pipeline</h1>
                <p class="mt-1 text-sm text-cream-dim">Each card is a saved lead. Changing the status writes it to that lead.</p>
            </div>
            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                @foreach (\App\Enums\InquiryStatus::cases() as $status)
                    @php($column = $this->inquiries->where('status', $status))
                    <div class="rounded-xl border border-line bg-ink p-3">
                        <h2 class="mb-3 flex items-center justify-between px-1 text-[11px] tracking-[0.14em] text-cream-dim uppercase">
                            {{ $status->label() }}
                            <span>{{ $column->count() }}</span>
                        </h2>
                        @forelse ($column as $inquiry)
                            <article wire:key="pipe-{{ $inquiry->id }}" class="mb-2 rounded-lg border border-line bg-panel p-3">
                                <button type="button" wire:click="open({{ $inquiry->id }})" class="block w-full text-left">
                                    <span class="block text-sm text-cream">{{ $inquiry->name }}</span>
                                    <span class="mt-1 block text-xs text-cream-dim">{{ $inquiry->car?->name ?? 'No car attached' }}</span>
                                    <span class="mt-2 inline-flex rounded-full border border-gold/40 px-2 py-0.5 text-[10px] tracking-[0.12em] text-gold uppercase">{{ $inquiry->type->label() }}</span>
                                </button>
                                <select wire:change="updateStatus({{ $inquiry->id }}, $event.target.value)" class="mt-3 w-full border border-line bg-ink px-2 py-1.5 text-sm text-cream">
                                    @foreach (\App\Enums\InquiryStatus::cases() as $option)
                                        <option value="{{ $option->value }}" @selected($inquiry->status === $option)>{{ $option->label() }}</option>
                                    @endforeach
                                </select>
                            </article>
                        @empty
                            <p class="px-1 text-sm text-cream-dim">None.</p>
                        @endforelse
                    </div>
                @endforeach
            </div>
        @elseif ($screen === 'tasks')
            <div>
                <h1 class="font-display text-2xl text-cream">My Tasks</h1>
                <p class="mt-1 text-sm text-cream-dim">These belong to the signed-in account. There is no list of other staff yet.</p>
            </div>
            <form wire:submit="saveTask" class="grid gap-3 rounded-xl border border-line bg-panel p-4 sm:grid-cols-2">
                <label class="flex flex-col gap-2 text-[11px] tracking-[0.14em] text-cream-dim uppercase sm:col-span-2">
                    Title
                    <input wire:model="taskTitle" type="text" class="border border-line bg-ink px-3 py-2 text-sm tracking-normal text-cream normal-case">
                    @error('taskTitle')
                        <span class="text-[12px] tracking-normal text-gold normal-case">{{ $message }}</span>
                    @enderror
                </label>
                <label class="flex flex-col gap-2 text-[11px] tracking-[0.14em] text-cream-dim uppercase">
                    Due date
                    <input wire:model="taskDue" type="date" class="border border-line bg-ink px-3 py-2 text-sm tracking-normal text-cream normal-case">
                    @error('taskDue')
                        <span class="text-[12px] tracking-normal text-gold normal-case">{{ $message }}</span>
                    @enderror
                </label>
                <label class="flex flex-col gap-2 text-[11px] tracking-[0.14em] text-cream-dim uppercase">
                    Lead
                    <select wire:model="taskInquiryId" class="border border-line bg-ink px-3 py-2 text-sm tracking-normal text-cream normal-case">
                        <option value="">No lead</option>
                        @foreach ($this->inquiries as $inquiry)
                            <option value="{{ $inquiry->id }}">{{ $inquiry->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="flex flex-col gap-2 text-[11px] tracking-[0.14em] text-cream-dim uppercase sm:col-span-2">
                    Note
                    <textarea wire:model="taskNotes" rows="2" class="border border-line bg-ink px-3 py-2 text-sm tracking-normal text-cream normal-case"></textarea>
                </label>
                <button type="submit" class="inline-flex self-start bg-gold px-4 py-2 text-[12px] tracking-[0.14em] text-ink uppercase" wire:loading.attr="disabled" wire:target="saveTask">
                    <span wire:loading.remove wire:target="saveTask">Add task</span>
                    <span wire:loading wire:target="saveTask">Saving</span>
                </button>
            </form>
            <div class="flex flex-wrap gap-2">
                @foreach (['open' => 'Open', 'done' => 'Done', 'overdue' => 'Overdue'] as $filter => $label)
                    <button
                        type="button"
                        wire:click="$set('taskFilter', '{{ $filter }}')"
                        @class([
                            'rounded-full px-3 py-1.5 text-[11px] tracking-[0.14em] uppercase',
                            'bg-gold text-ink' => $taskFilter === $filter,
                            'border border-line text-cream-dim' => $taskFilter !== $filter,
                        ])
                    >{{ $label }}</button>
                @endforeach
            </div>
            <div class="flex flex-col gap-2">
                @forelse ($this->visibleTasks as $task)
                    <article wire:key="task-{{ $task->id }}" class="rounded-xl border border-line bg-panel px-4 py-3">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <p class="text-sm text-cream">{{ $task->title }}</p>
                                <p class="mt-1 text-xs text-cream-dim">
                                    {{ $task->status->label() }}
                                    @if ($task->due_on)
                                        · {{ $task->due_on->format('j M Y') }}
                                    @endif
                                    @if ($task->inquiry)
                                        · {{ $task->inquiry->name }}
                                    @endif
                                </p>
                                @if ($task->notes)
                                    <p class="mt-1 text-sm text-cream-dim">{{ $task->notes }}</p>
                                @endif
                            </div>
                            @if ($task->status === \App\Enums\TaskStatus::Open)
                                <button type="button" wire:click="completeTask({{ $task->id }})" class="text-[11px] tracking-[0.14em] text-gold uppercase" wire:loading.attr="disabled" wire:target="completeTask">
                                    <span wire:loading.remove wire:target="completeTask">Mark done</span>
                                    <span wire:loading wire:target="completeTask">Saving</span>
                                </button>
                            @endif
                        </div>
                    </article>
                @empty
                    <p class="text-sm text-cream-dim">No tasks in this view.</p>
                @endforelse
            </div>
        @elseif ($screen === 'cal')
            <div>
                <h1 class="font-display text-2xl text-cream">Calendar</h1>
                <p class="mt-1 max-w-2xl text-sm text-cream-dim">November 2026 is shown as a layout. A request saved on the website does not mark a day as taken.</p>
            </div>
            <div class="rounded-xl border border-line bg-panel p-4">
                <div class="grid grid-cols-7 gap-1.5">
                    @foreach (['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $weekday)
                        <p class="py-1 text-center text-[11px] tracking-[0.12em] text-cream-dim uppercase">{{ $weekday }}</p>
                    @endforeach
                    @for ($blank = 0; $blank < $this->calendar['blank']; $blank++)
                        <div></div>
                    @endfor
                    @for ($date = 1; $date <= $this->calendar['days']; $date++)
                        <button
                            type="button"
                            wire:click="$set('day', {{ $date }})"
                            @class([
                                'min-h-14 rounded-lg border p-2 text-left text-sm',
                                'border-gold text-gold' => $day === $date || $this->calendar['today'] === $date,
                                'border-line text-cream' => $day !== $date && $this->calendar['today'] !== $date,
                            ])
                        >{{ $date }}</button>
                    @endfor
                </div>
            </div>
            <div class="rounded-xl border border-line bg-panel p-4">
                <h2 class="text-sm text-cream">{{ $day ? $day.' November 2026' : 'Choose a day' }}</h2>
                <p class="mt-2 text-sm text-cream-dim">No bookings are stored for this day.</p>
            </div>
        @elseif ($screen === 'reports')
            <div>
                <h1 class="font-display text-2xl text-cream">Reports</h1>
                <p class="mt-1 text-sm text-cream-dim">Counts from the leads and tasks already saved. No sales totals are stored.</p>
            </div>
            <div class="grid gap-3 lg:grid-cols-3">
                <section class="rounded-xl border border-line bg-panel p-4">
                    <h2 class="mb-3 text-sm text-cream">By status</h2>
                    @foreach (\App\Enums\InquiryStatus::cases() as $status)
                        <div class="flex justify-between border-t border-line py-2 text-sm first:border-t-0">
                            <span class="text-cream-dim">{{ $status->label() }}</span>
                            <span>{{ $this->statusCounts[$status->value] }}</span>
                        </div>
                    @endforeach
                </section>
                <section class="rounded-xl border border-line bg-panel p-4">
                    <h2 class="mb-3 text-sm text-cream">By source</h2>
                    @foreach (\App\Enums\InquiryType::cases() as $inquiryType)
                        <div class="flex justify-between border-t border-line py-2 text-sm first:border-t-0">
                            <span class="text-cream-dim">{{ $inquiryType->label() }}</span>
                            <span>{{ $this->typeCounts[$inquiryType->value] }}</span>
                        </div>
                    @endforeach
                </section>
                <section class="rounded-xl border border-line bg-panel p-4">
                    <h2 class="mb-3 text-sm text-cream">Tasks</h2>
                    @foreach (['Open' => $this->stats['open'], 'Done' => $this->stats['done'], 'Overdue' => $this->stats['overdue']] as $label => $value)
                        <div class="flex justify-between border-t border-line py-2 text-sm first:border-t-0">
                            <span class="text-cream-dim">{{ $label }}</span>
                            <span>{{ $value }}</span>
                        </div>
                    @endforeach
                </section>
            </div>
        @elseif ($screen === 'team')
            <div>
                <h1 class="font-display text-2xl text-cream">Sales team</h1>
                <p class="mt-1 max-w-2xl text-sm text-cream-dim">These cards are a sample of the layout. They are not people in the studio, and leads are not assigned to them.</p>
            </div>
            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                @foreach (['A', 'B', 'C'] as $seat)
                    <article class="rounded-xl border border-line bg-panel p-4">
                        <div class="flex items-center gap-3">
                            <span class="grid size-8 place-items-center rounded-full bg-gold text-sm text-ink">{{ $seat }}</span>
                            <div>
                                <p class="text-sm text-cream">Sample advisor {{ $seat }}</p>
                                <p class="text-xs text-cream-dim">Not saved</p>
                            </div>
                        </div>
                        <dl class="mt-3 flex flex-col gap-1 text-sm">
                            <div class="flex justify-between"><dt class="text-cream-dim">Active leads</dt><dd>0</dd></div>
                            <div class="flex justify-between"><dt class="text-cream-dim">Test drives</dt><dd>0</dd></div>
                            <div class="flex justify-between"><dt class="text-cream-dim">Deals closed</dt><dd>0</dd></div>
                        </dl>
                    </article>
                @endforeach
            </div>
        @elseif ($screen === 'stock')
            <div class="flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h1 class="font-display text-2xl text-cream">Cars and prices</h1>
                    <p class="mt-1 max-w-2xl text-sm text-cream-dim">These are the cars already on the desk. Change a price or a status there.</p>
                </div>
                <a href="{{ route('admin.cars') }}" class="text-[11px] tracking-[0.14em] text-gold uppercase">Open the car desk</a>
            </div>
            <div class="overflow-x-auto rounded-xl border border-line bg-panel">
                <table class="w-full min-w-[40rem] text-left text-sm">
                    <thead class="text-[11px] tracking-[0.14em] text-cream-dim uppercase">
                        <tr>
                            <th class="px-3 py-2">Car</th>
                            <th class="px-3 py-2">Condition</th>
                            <th class="px-3 py-2">Price</th>
                            <th class="px-3 py-2">Status</th>
                            <th class="px-3 py-2">Leads</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($this->cars as $car)
                            <tr class="border-t border-line">
                                <td class="px-3 py-2">
                                    <span class="flex items-center gap-3">
                                        @if ($image = $car->galleryImages->first())
                                            <img src="{{ asset($image->path) }}" alt="" class="h-10 w-14 rounded object-cover">
                                        @endif
                                        <span>{{ $car->brand->name }} {{ $car->name }}</span>
                                    </span>
                                </td>
                                <td class="px-3 py-2 text-cream-dim">{{ $car->condition->label() }}</td>
                                <td class="px-3 py-2">{{ $car->priceLabel() }}</td>
                                <td class="px-3 py-2">{{ $car->status->label() }}</td>
                                <td class="px-3 py-2">{{ $this->inquiries->where('car_id', $car->id)->count() }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-3 py-6 text-cream-dim">No cars yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @else
            <div>
                <h1 class="font-display text-2xl text-cream">Announcements</h1>
                <p class="mt-1 max-w-2xl text-sm text-cream-dim">This is the layout for a notice. It is not sent, and it is not saved.</p>
            </div>
            <div class="grid gap-3 lg:grid-cols-2">
                <div class="rounded-xl border border-line bg-panel p-4">
                    <h2 class="text-sm text-cream">Draft</h2>
                    <div class="mt-3 flex flex-col gap-3">
                        <label class="text-[11px] tracking-[0.14em] text-cream-dim uppercase">Title
                            <input type="text" value="New arrival" disabled class="mt-1 w-full border border-line bg-ink px-3 py-2 text-sm tracking-normal text-cream normal-case">
                        </label>
                        <label class="text-[11px] tracking-[0.14em] text-cream-dim uppercase">Message
                            <textarea rows="3" disabled class="mt-1 w-full border border-line bg-ink px-3 py-2 text-sm tracking-normal text-cream normal-case">The studio will write the notice here.</textarea>
                        </label>
                    </div>
                </div>
                <div class="rounded-xl border border-line bg-panel p-4">
                    <h2 class="text-sm text-cream">Sample list</h2>
                    <ul class="mt-3 flex flex-col gap-3 text-sm">
                        <li><p class="text-cream">Sample notice</p><p class="text-cream-dim">Not sent</p></li>
                        <li><p class="text-cream">Sample banner</p><p class="text-cream-dim">Not on the website</p></li>
                    </ul>
                </div>
            </div>
        @endif
    </div>

    @if ($inquiry = $this->openInquiry)
        <div class="fixed inset-y-0 right-0 z-50 w-full max-w-md overflow-y-auto border-l border-line bg-panel p-6">
            <button type="button" wire:click="closeLead" class="text-[11px] tracking-[0.14em] text-gold uppercase">Close</button>
            <p class="mt-4 text-[11px] tracking-[0.14em] text-gold uppercase">{{ $inquiry->type->label() }}</p>
            <h2 class="mt-1 font-display text-3xl text-cream">{{ $inquiry->name }}</h2>
            <dl class="mt-4 flex flex-col gap-3 text-sm">
                <div>
                    <dt class="text-[11px] tracking-[0.14em] text-cream-dim uppercase">Phone</dt>
                    <dd class="text-cream">{{ $inquiry->phone }}</dd>
                </div>
                <div>
                    <dt class="text-[11px] tracking-[0.14em] text-cream-dim uppercase">Saved</dt>
                    <dd class="text-cream">{{ $inquiry->created_at?->format('j M Y, H:i') }}</dd>
                </div>
                <div>
                    <dt class="text-[11px] tracking-[0.14em] text-cream-dim uppercase">Vehicle</dt>
                    <dd class="text-cream">
                        @if ($inquiry->car)
                            <a href="{{ route('cars.show', $inquiry->car) }}" class="hover:text-gold">{{ $inquiry->car->brand->name }} {{ $inquiry->car->name }}</a>
                        @else
                            No car attached
                        @endif
                    </dd>
                </div>
            </dl>
            <label class="mt-4 flex flex-col gap-2 text-[11px] tracking-[0.14em] text-cream-dim uppercase">
                Status
                <select wire:change="updateStatus({{ $inquiry->id }}, $event.target.value)" class="border border-line bg-ink px-3 py-2 text-sm tracking-normal text-cream normal-case">
                    @foreach (\App\Enums\InquiryStatus::cases() as $option)
                        <option value="{{ $option->value }}" @selected($inquiry->status === $option)>{{ $option->label() }}</option>
                    @endforeach
                </select>
            </label>
            <div class="mt-4">
                <p class="text-[11px] tracking-[0.14em] text-cream-dim uppercase">Saved message</p>
                @if ($inquiry->message)
                    <p class="mt-2 text-sm leading-relaxed whitespace-pre-line text-cream-dim">{{ $inquiry->message }}</p>
                @else
                    <p class="mt-2 text-sm text-cream-dim">No message was saved.</p>
                @endif
            </div>
            <div class="mt-4">
                <p class="text-[11px] tracking-[0.14em] text-cream-dim uppercase">Related tasks</p>
                <div class="mt-2 flex flex-col gap-2">
                    @forelse ($inquiry->tasks as $task)
                        <div class="rounded-lg border border-line px-3 py-2 text-sm">
                            <p class="text-cream">{{ $task->title }}</p>
                            <p class="text-xs text-cream-dim">{{ $task->status->label() }}@if ($task->due_on) · {{ $task->due_on->format('j M Y') }}@endif</p>
                        </div>
                    @empty
                        <p class="text-sm text-cream-dim">No tasks for this lead.</p>
                    @endforelse
                </div>
            </div>
            <p class="mt-4 text-sm text-cream-dim">There is no activity history yet.</p>
        </div>
    @endif
</div>
