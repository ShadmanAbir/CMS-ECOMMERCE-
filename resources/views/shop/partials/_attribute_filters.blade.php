{{-- Dynamic attribute filters driven by per-site product_schema (is_filterable) --}}
@if($filterableAttributes->isNotEmpty())
    @foreach($filterableAttributes as $attr)
        @php
            $options = $attr->options ?? [];
            $active = $attrFilters[$attr->code] ?? [];
            $active = is_array($active) ? $active : [$active];
        @endphp

        @if(count($options) > 0)
        <div>
            <h3 class="text-sm font-bold {{ $cardText }} uppercase tracking-widest mb-3">{{ $attr->label }}</h3>
            <div class="space-y-1.5">
                @foreach($options as $option)
                    @php
                        $isActive = in_array($option, $active);
                        // Build query with this option toggled
                        $newAttr = $attrFilters;
                        if ($isActive) {
                            $newAttr[$attr->code] = array_values(array_diff($newAttr[$attr->code] ?? [], [$option]));
                            if (empty($newAttr[$attr->code])) unset($newAttr[$attr->code]);
                        } else {
                            $newAttr[$attr->code] = array_values(array_merge($newAttr[$attr->code] ?? [], [$option]));
                        }
                        $href = route('product.search', array_merge(request()->query(), ['attr' => $newAttr ?: null]));
                    @endphp
                    <a href="{{ $href }}"
                       class="flex items-center gap-2.5 px-3 py-2 text-sm border rounded transition
                              {{ $isActive ? 'bg-accent/10 border-accent text-accent' : $muted . ' border-transparent hover:border-accent/30 hover:' . $cardText }}">
                        <span class="w-4 h-4 flex-shrink-0 rounded-sm border flex items-center justify-center
                                     {{ $isActive ? 'bg-accent border-accent' : $inputBorder }}">
                            @if($isActive)
                                <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                </svg>
                            @endif
                        </span>
                        {{ $option }}
                    </a>
                @endforeach
            </div>
        </div>
        @endif
    @endforeach
@endif
