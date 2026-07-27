{{-- Dynamic per-site attributes driven by product_schema config --}}
@if($schemaAttributes->isNotEmpty())
    @php
        $values = $attributeValues ?? [];
    @endphp

    <div class="md:col-span-2 border-t {{ $aBorderDiv }} pt-6">
        <h3 class="text-lg font-heading tracking-wide {{ $aCardText }} mb-1">SITE <span class="text-accent">ATTRIBUTES</span></h3>
        <p class="text-sm {{ $aMuted2 }} mb-4">Fields specific to this storefront. Managed via <code class="text-xs">site.php → product_schema</code></p>
    </div>

    @foreach($schemaAttributes as $attr)
        @php
            $fieldValue = old("attributes.{$attr->code}", $values[$attr->code]['value'] ?? '');
            $options = $attr->options ?? [];
            $selectedMulti = is_string($fieldValue) && str_starts_with($fieldValue, '[')
                ? (json_decode($fieldValue, true) ?? [])
                : (is_array($fieldValue) ? $fieldValue : []);
        @endphp

        <div class="{{ in_array($attr->type, ['textarea']) ? 'md:col-span-2' : '' }}">
            <label class="block text-sm font-medium {{ $aMuted3 }} mb-2 uppercase tracking-wider">
                {{ $attr->label }}
                @if($attr->is_required) * @endif
                @if($attr->is_filterable)
                    <span class="ml-1 text-[10px] px-1.5 py-0.5 rounded bg-accent/10 text-accent font-semibold normal-case tracking-normal">filterable</span>
                @endif
            </label>

            @switch($attr->type)
                @case('textarea')
                    <textarea name="attributes[{{ $attr->code }}]" rows="3"
                              {{ $attr->is_required ? 'required' : '' }}
                              class="w-full px-4 py-2 {{ $aInputBg }} border {{ $aInputBorder }} rounded-lg {{ $aInputText }} focus:outline-none focus:border-accent transition">{{ $fieldValue }}</textarea>
                    @break

                @case('select')
                    <select name="attributes[{{ $attr->code }}]"
                            {{ $attr->is_required ? 'required' : '' }}
                            class="w-full px-4 py-2 {{ $aInputBg }} border {{ $aInputBorder }} rounded-lg {{ $aInputText }} focus:outline-none focus:border-accent transition">
                        <option value="">— Select {{ $attr->label }} —</option>
                        @foreach($options as $option)
                            <option value="{{ $option }}" {{ $fieldValue === $option ? 'selected' : '' }}>{{ $option }}</option>
                        @endforeach
                    </select>
                    @break

                @case('multiselect')
                    <select name="attributes[{{ $attr->code }}][]" multiple
                            {{ $attr->is_required ? 'required' : '' }}
                            class="w-full px-4 py-2 {{ $aInputBg }} border {{ $aInputBorder }} rounded-lg {{ $aInputText }} focus:outline-none focus:border-accent transition min-h-[100px]">
                        @foreach($options as $option)
                            <option value="{{ $option }}" {{ in_array($option, $selectedMulti) ? 'selected' : '' }}>{{ $option }}</option>
                        @endforeach
                    </select>
                    <p class="text-sm {{ $aMuted2 }} mt-1">Hold Ctrl/Cmd to select multiple</p>
                    @break

                @case('boolean')
                    <label class="flex items-center space-x-3 p-3 border {{ $aFlagBorder }} rounded-lg {{ $aFlagHover }} cursor-pointer">
                        <input type="hidden" name="attributes[{{ $attr->code }}]" value="0">
                        <input type="checkbox" name="attributes[{{ $attr->code }}]" value="1"
                               {{ $fieldValue ? 'checked' : '' }}
                               class="w-5 h-5 text-accent {{ $aCheckBorder }} rounded focus:ring-accent {{ $aCheckBg }}">
                        <span class="text-sm font-medium {{ $aCardText }}">Yes</span>
                    </label>
                    @break

                @case('number')
                    <input type="number" name="attributes[{{ $attr->code }}]" step="any"
                           value="{{ $fieldValue }}" {{ $attr->is_required ? 'required' : '' }}
                           class="w-full px-4 py-2 {{ $aInputBg }} border {{ $aInputBorder }} rounded-lg {{ $aInputText }} focus:outline-none focus:border-accent transition">
                    @break

                @default
                    <input type="text" name="attributes[{{ $attr->code }}]"
                           value="{{ $fieldValue }}" {{ $attr->is_required ? 'required' : '' }}
                           class="w-full px-4 py-2 {{ $aInputBg }} border {{ $aInputBorder }} rounded-lg {{ $aInputText }} focus:outline-none focus:border-accent transition">
            @endswitch

            @error("attributes.{$attr->code}")
                <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>
    @endforeach
@endif
