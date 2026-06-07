<li class="flex items-center gap-1">
    <button type="button" role="option" aria-selected="false"
        class="{{ $itemClass ?? 'field-option' }} flex min-w-0 flex-1 items-center justify-between gap-2 rounded-xl px-3 py-2.5 text-left transition hover:bg-[#F1F8E9] aria-selected:bg-[#E8F5E9] aria-selected:ring-1 aria-selected:ring-[#4CAF50]/40"
        data-value="{{ $field->id }}"
        data-price="{{ $field->price_per_hour }}"
        data-lat="{{ $field->latitude }}"
        data-lng="{{ $field->longitude }}"
        data-name="{{ $field->name }}"
        data-detail="{{ route('fields.show', $field->id) }}"
        data-label="{{ $field->name }} - Rp {{ number_format($field->price_per_hour, 0, ',', '.') }}/jam"
        data-search="{{ strtolower($field->name . ' ' . $field->city . ' ' . $field->address) }}">
        <span class="min-w-0 flex-1">
            <span class="block truncate font-bold text-[#0B5D1E]">{{ $field->name }}</span>
            <span class="block truncate text-xs font-semibold text-[#4B8B43]">
                {{ $field->city }}@if(! is_null($field->distance_km)) • {{ number_format($field->distance_km, 1) }} km @endif
            </span>
        </span>
        <span class="shrink-0 whitespace-nowrap rounded-lg bg-[#F1F8E9] px-2 py-1 text-xs font-black text-[#2E7D32]">
            Rp {{ number_format($field->price_per_hour, 0, ',', '.') }}/jam
        </span>
    </button>
    <a href="{{ route('fields.show', $field->id) }}" target="_blank" title="Detail lapangan"
        class="grid h-8 w-8 shrink-0 place-items-center rounded-full text-[#4B8B43] transition hover:bg-[#E8F5E9] hover:text-[#2E7D32]">
        <x-heroicon-o-information-circle class="h-5 w-5" />
    </a>
</li>
