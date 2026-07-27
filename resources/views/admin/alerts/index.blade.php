@extends('layouts.admin')

@section('title', 'Inventory Alerts')
@section('page-title', 'Low-Stock Alerts')

@section('content')
@php
    $aCardBg    = theme('admin.card_bg');
    $aCardBorder = theme('admin.card_border');
    $aCardText  = theme('admin.card_text');
    $aMuted     = theme('admin.muted_text');
    $aMuted2    = theme('admin.muted_text_2');
    $aBorderDiv = theme('admin.border_divider');
@endphp

{{-- Filter Tabs --}}
<div class="flex items-center gap-2 mb-6">
    @php
        $openCount = $counts['unresolved'];
        $tabs = [
            'unresolved' => "Open ({$counts['unresolved']})",
            'resolved' => "Resolved ({$counts['resolved']})",
            'all' => "All ({$counts['all']})",
        ];
    @endphp
    @foreach($tabs as $key => $label)
        <a href="{{ route('admin.alerts.index', ['filter' => $key]) }}"
           class="px-4 py-2 rounded-lg text-sm font-medium transition
                  {{ $filter === $key ? 'bg-accent text-white' : "{$aCardBg} {$aCardBorder} {$aMuted} hover:text-white" }}">
            {{ $label }}
        </a>
    @endforeach

    @if($openCount > 0)
        <form method="POST" action="{{ route('admin.alerts.resolve-all') }}" class="ml-auto"
              onsubmit="return confirm('Resolve all {{ $openCount }} open alerts?');">
            @csrf
            <button type="submit" class="px-4 py-2 rounded-lg text-sm font-medium bg-green-500/10 border border-green-500/20 text-green-400 hover:bg-green-500/20 transition">
                Resolve All ({{ $openCount }})
            </button>
        </form>
    @endif
</div>

{{-- Alerts Table --}}
<div class="{{ $aCardBg }} {{ $aCardBorder }} rounded-lg overflow-hidden">
    @if($alerts->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b {{ $aBorderDiv }}">
                        <th class="px-6 py-4 text-xs uppercase tracking-wider {{ $aMuted }}">Product</th>
                        <th class="px-6 py-4 text-xs uppercase tracking-wider {{ $aMuted }}">Stock at Alert</th>
                        <th class="px-6 py-4 text-xs uppercase tracking-wider {{ $aMuted }}">Current Stock</th>
                        <th class="px-6 py-4 text-xs uppercase tracking-wider {{ $aMuted }}">Threshold</th>
                        <th class="px-6 py-4 text-xs uppercase tracking-wider {{ $aMuted }}">Status</th>
                        <th class="px-6 py-4 text-xs uppercase tracking-wider {{ $aMuted }}">Raised</th>
                        <th class="px-6 py-4 text-xs uppercase tracking-wider {{ $aMuted }}">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y {{ $aBorderDiv }}">
                    @foreach($alerts as $alert)
                        <tr class="hover:bg-white/5 transition">
                            <td class="px-6 py-4">
                                @if($alert->product)
                                    <a href="{{ route('admin.products.edit', $alert->product) }}" class="{{ $aCardText }} text-sm font-medium hover:text-accent transition">
                                        {{ Str::limit($alert->product->name, 30) }}
                                    </a>
                                    <p class="{{ $aMuted }} text-xs">{{ $alert->product->sku }}</p>
                                @else
                                    <span class="{{ $aMuted }} text-sm italic">Deleted Product</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs rounded-full bg-red-500/20 text-red-300">{{ $alert->stock_at_alert }}</span>
                            </td>
                            <td class="px-6 py-4 {{ $aCardText }} text-sm">
                                {{ $alert->product?->totalStock() ?? '—' }}
                            </td>
                            <td class="px-6 py-4 {{ $aMuted }} text-sm">≤ {{ $alert->threshold }}</td>
                            <td class="px-6 py-4">
                                @if($alert->is_resolved)
                                    <span class="px-2 py-1 text-xs rounded-full bg-green-500/20 text-green-300">Resolved</span>
                                @else
                                    <span class="px-2 py-1 text-xs rounded-full bg-yellow-500/20 text-yellow-300">Open</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 {{ $aMuted }} text-sm whitespace-nowrap">
                                {{ $alert->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    @unless($alert->is_resolved)
                                        <form action="{{ route('admin.alerts.resolve', $alert) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 text-xs rounded-lg bg-green-500/20 text-green-300 hover:bg-green-500/30 transition" title="Resolve">
                                                ✓ Resolve
                                            </button>
                                        </form>
                                    @endunless

                                    <form action="{{ route('admin.alerts.destroy', $alert) }}" method="POST" onsubmit="return confirm('Delete this alert permanently?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-3 py-1.5 text-xs rounded-lg bg-red-500/20 text-red-300 hover:bg-red-500/30 transition" title="Delete">
                                            🗑
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="px-6 py-4 border-t {{ $aBorderDiv }}">
            {{ $alerts->links() }}
        </div>
    @else
        <div class="p-12 text-center">
            <p class="{{ $aMuted2 }}">No {{ $filter !== 'all' ? $filter : '' }} stock alerts.</p>
        </div>
    @endif
</div>
@endsection
