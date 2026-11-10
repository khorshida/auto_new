<div class="overflow-x-auto">
    <table class="w-full min-w-[46rem] text-left text-sm">
        <thead class="text-[11px] tracking-[0.14em] text-cream-dim uppercase">
            <tr>
                <th class="px-3 py-2">Customer</th>
                <th class="px-3 py-2">Phone</th>
                <th class="px-3 py-2">Source</th>
                <th class="px-3 py-2">Car</th>
                <th class="px-3 py-2">Status</th>
                <th class="px-3 py-2">Saved</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($leads as $inquiry)
                <tr wire:key="crm-lead-{{ $inquiry->id }}" class="border-t border-line">
                    <td class="px-3 py-2.5">
                        <button type="button" wire:click="open({{ $inquiry->id }})" class="text-left text-cream hover:text-gold">{{ $inquiry->name }}</button>
                    </td>
                    <td class="px-3 py-2.5 text-cream-dim">{{ $inquiry->phone }}</td>
                    <td class="px-3 py-2.5">
                        <span class="inline-flex rounded-full border border-gold/40 px-2 py-0.5 text-[10px] tracking-[0.12em] text-gold uppercase">{{ $inquiry->type->label() }}</span>
                    </td>
                    <td class="px-3 py-2.5 text-cream-dim">{{ $inquiry->car?->name ?? 'No car attached' }}</td>
                    <td class="px-3 py-2.5">
                        <select wire:change="updateStatus({{ $inquiry->id }}, $event.target.value)" class="border border-line bg-ink px-2 py-1.5 text-sm text-cream">
                            @foreach (\App\Enums\InquiryStatus::cases() as $option)
                                <option value="{{ $option->value }}" @selected($inquiry->status === $option)>{{ $option->label() }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td class="px-3 py-2.5 text-cream-dim">{{ $inquiry->created_at?->format('j M Y') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-3 py-6 text-cream-dim">No leads match this view.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
