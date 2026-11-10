@php
    $primary = [
        'dash' => 'Overview',
        'leads' => 'Leads',
        'pipe' => 'Sales Pipeline',
        'tasks' => 'My Tasks',
        'cal' => 'Calendar',
        'reports' => 'Reports',
    ];
    $secondary = [
        'stock' => 'Cars and prices',
        'team' => 'Sales team',
        'ann' => 'Announcements',
    ];
@endphp

<nav class="flex flex-col gap-1" aria-label="CRM">
    @foreach ($primary as $key => $label)
        <button
            type="button"
            wire:click="show('{{ $key }}')"
            @class([
                'flex items-center gap-2 rounded-lg px-3 py-2 text-left text-sm',
                'bg-gold/10 text-gold' => $screen === $key,
                'text-cream-dim hover:text-cream' => $screen !== $key,
            ])
        >
            @include('partials.admin.crm-icon', ['icon' => $key])
            {{ $label }}
        </button>
    @endforeach

    <p class="mt-4 px-3 text-[10px] tracking-[0.16em] text-cream-dim uppercase">Also on the desk</p>
    @foreach ($secondary as $key => $label)
        <button
            type="button"
            wire:click="show('{{ $key }}')"
            @class([
                'flex items-center gap-2 rounded-lg px-3 py-2 text-left text-sm',
                'bg-gold/10 text-gold' => $screen === $key,
                'text-cream-dim hover:text-cream' => $screen !== $key,
            ])
        >
            @include('partials.admin.crm-icon', ['icon' => $key])
            <span class="min-w-0">
                {{ $label }}
                @if (in_array($key, ['team', 'ann'], true))
                    <span class="mt-0.5 block text-[10px] tracking-[0.12em] uppercase">Sample</span>
                @endif
            </span>
        </button>
    @endforeach

    <a href="{{ route('admin.cars') }}" class="mt-4 flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-cream-dim hover:text-gold">
        @include('partials.admin.crm-icon', ['icon' => 'desk'])
        Back to the car desk
    </a>
</nav>
