@php
    $path = match ($icon) {
        'dash' => 'M4 5h7v7H4V5zm9 0h7v4h-7V5zM4 14h7v5H4v-5zm9-3h7v8h-7v-8z',
        'leads' => 'M8 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm8 1a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5zM3 19c.4-2.4 2.4-4 5-4s4.6 1.6 5 4m1-3.5c1.8.2 3.3 1.4 3.8 3.5',
        'pipe' => 'M4 5h4v14H4V5zm6 3h4v11h-4V8zm6-2h4v13h-4V6z',
        'tasks' => 'M5 7h9M5 12h9M5 17h6m6.5-9.5 1.5 1.5L20 6',
        'cal' => 'M7 4v2m10-2v2M5 8h14v12H5V8zm0 4h14',
        'reports' => 'M5 19V10m5 9V5m5 14v-7m5 7V8',
        'stock' => 'M4 15h16l-2-6H6l-2 6zm2 0v3m12-3v3M8 12h8',
        'team' => 'M12 12a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7zM5 20c.6-3 3.2-5 7-5s6.4 2 7 5',
        'ann' => 'M5 10v4h3l5 3V7L8 10H5zm11 1a3 3 0 0 1 0 2',
        default => 'M5 12h14M13 6l6 6-6 6',
    };
@endphp

<svg class="size-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}" />
</svg>
