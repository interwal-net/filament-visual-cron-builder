{{-- One cron position: mode control + mode-specific inputs.
     Inherits scope from cron-builder.blade.php: $statePath, $key, $label,
     $column, $modeOptions, $wireModel, $chipCols, $showLabel and the
     $get*Options() view helpers. --}}
@php
    $mode = $column['mode'] ?? 'every';
    $selectedValues = array_map('strval', (array) ($column['values'] ?? []));
    $ranges = $column['ranges'] ?? [];
@endphp

@if ($showLabel)
    <span class="cb-card-label">{{ $label }}</span>
@endif

{{-- Mode: segmented control --}}
<div class="cb-modes" role="radiogroup" aria-label="{{ $label }}">
    @foreach ($modeOptions as $value => $modeLabel)
        <label @class(['cb-mode', 'cb-mode--active' => $mode === $value])>
            <input
                type="radio"
                class="cb-input"
                value="{{ $value }}"
                {{ $wireModel }}="{{ $statePath }}.{{ $key }}.mode"
                @checked($mode === $value)
            />
            {{ $modeLabel }}
        </label>
    @endforeach
</div>

@if ($mode === 'every')
    <p class="cb-any" wire:key="{{ $statePath }}.{{ $key }}.body.every">
        {{ __('cron-builder::cron-builder.fields.any') }} (<code>*</code>)
    </p>
@elseif ($mode === 'specific')
    {{-- Value chips: click to toggle --}}
    <div class="cb-chips" wire:key="{{ $statePath }}.{{ $key }}.body.specific" style="grid-template-columns: repeat({{ $chipCols[$key] ?? 6 }}, minmax(0, 1fr))">
        @foreach ($getChipOptions($key) as $value => $optionLabel)
            @php($selected = in_array((string) $value, $selectedValues, true))
            <label @class(['cb-chip', 'cb-chip--on' => $selected])>
                <input
                    type="checkbox"
                    class="cb-input"
                    value="{{ $value }}"
                    {{ $wireModel }}="{{ $statePath }}.{{ $key }}.values"
                    @checked($selected)
                />
                {{ $optionLabel }}
            </label>
        @endforeach
    </div>
@elseif ($mode === 'range')
    {{-- Filled rows plus one blank row; filling the blank row adds another --}}
    <div class="cb-rows" wire:key="{{ $statePath }}.{{ $key }}.body.range">
        @foreach ([...$ranges, ['from' => null, 'to' => null]] as $index => $range)
            <div class="cb-row" wire:key="{{ $statePath }}.{{ $key }}.ranges.{{ $index }}">
                <select {{ $wireModel }}="{{ $statePath }}.{{ $key }}.ranges.{{ $index }}.from" class="cb-select">
                    <option value="">{{ __('cron-builder::cron-builder.fields.from') }}</option>
                    @foreach ($getValueOptions($key) as $value => $optionLabel)
                        <option value="{{ $value }}" @selected((string) $value === (string) ($range['from'] ?? ''))>{{ $optionLabel }}</option>
                    @endforeach
                </select>
                <span class="cb-row-sep">-</span>
                <select {{ $wireModel }}="{{ $statePath }}.{{ $key }}.ranges.{{ $index }}.to" class="cb-select">
                    <option value="">{{ __('cron-builder::cron-builder.fields.to') }}</option>
                    @foreach ($getValueOptions($key) as $value => $optionLabel)
                        <option value="{{ $value }}" @selected((string) $value === (string) ($range['to'] ?? ''))>{{ $optionLabel }}</option>
                    @endforeach
                </select>
            </div>
        @endforeach
    </div>
@elseif ($mode === 'step')
    <div class="cb-row" wire:key="{{ $statePath }}.{{ $key }}.body.step">
        <span class="cb-row-label">{{ __('cron-builder::cron-builder.fields.step') }}</span>
        <select {{ $wireModel }}="{{ $statePath }}.{{ $key }}.step" class="cb-select">
            <option value="">-</option>
            @foreach ($getStepOptions($key) as $value => $optionLabel)
                <option value="{{ $value }}" @selected((string) $value === (string) ($column['step'] ?? ''))>{{ $optionLabel }}</option>
            @endforeach
        </select>
    </div>
@endif
