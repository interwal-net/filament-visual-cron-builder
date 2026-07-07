@php
    $statePath = $getStatePath();
    $columns = $getCronColumns();
    $modeOptions = $getModeOptions();
    $positionLabels = $getPositionLabels();
    $selectClass = 'fi-input block w-full rounded-lg border-none bg-white py-1.5 px-3 text-sm text-gray-950 shadow-sm ring-1 ring-gray-950/10 focus:ring-2 focus:ring-primary-600 dark:bg-white/5 dark:text-white dark:ring-white/20';
@endphp

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div class="fi-cron-builder flex flex-col gap-4">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-5">
            @foreach ($positionLabels as $key => $label)
                @php($mode = $columns[$key]['mode'] ?? 'every')
                <div class="flex flex-col gap-2 rounded-xl border border-gray-200 p-3 dark:border-white/10">
                    <span class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        {{ $label }}
                    </span>

                    <select {{ $applyStateBindingModifiers('wire:model') }}="{{ $statePath }}.{{ $key }}.mode" class="{{ $selectClass }}">
                        @foreach ($modeOptions as $value => $modeLabel)
                            <option value="{{ $value }}">{{ $modeLabel }}</option>
                        @endforeach
                    </select>

                    @if ($mode === 'specific')
                        <select multiple {{ $applyStateBindingModifiers('wire:model') }}="{{ $statePath }}.{{ $key }}.values" class="{{ $selectClass }}" size="5">
                            @foreach ($getValueOptions($key) as $value => $optionLabel)
                                <option value="{{ $value }}">{{ $optionLabel }}</option>
                            @endforeach
                        </select>
                    @elseif ($mode === 'range')
                        <div class="flex items-center gap-2">
                            <select {{ $applyStateBindingModifiers('wire:model') }}="{{ $statePath }}.{{ $key }}.from" class="{{ $selectClass }}">
                                <option value="">{{ __('cron-builder::cron-builder.fields.from') }}</option>
                                @foreach ($getValueOptions($key) as $value => $optionLabel)
                                    <option value="{{ $value }}">{{ $optionLabel }}</option>
                                @endforeach
                            </select>
                            <span class="text-gray-400">-</span>
                            <select {{ $applyStateBindingModifiers('wire:model') }}="{{ $statePath }}.{{ $key }}.to" class="{{ $selectClass }}">
                                <option value="">{{ __('cron-builder::cron-builder.fields.to') }}</option>
                                @foreach ($getValueOptions($key) as $value => $optionLabel)
                                    <option value="{{ $value }}">{{ $optionLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                    @elseif ($mode === 'step')
                        <select {{ $applyStateBindingModifiers('wire:model') }}="{{ $statePath }}.{{ $key }}.step" class="{{ $selectClass }}">
                            <option value="">{{ __('cron-builder::cron-builder.fields.step') }}</option>
                            @foreach ($getStepOptions($key) as $value => $optionLabel)
                                <option value="{{ $value }}">{{ $optionLabel }}</option>
                            @endforeach
                        </select>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="flex flex-col gap-1 rounded-xl bg-gray-50 p-3 text-sm dark:bg-white/5">
            <div class="text-gray-700 dark:text-gray-300">
                <span class="font-semibold">{{ __('cron-builder::cron-builder.preview') }}:</span>
                {{ $getHumanReadable() }}
            </div>
            <div class="font-mono text-xs text-gray-500 dark:text-gray-400">
                <span class="font-semibold">{{ __('cron-builder::cron-builder.expression') }}:</span>
                {{ $getComposedExpression() }}
            </div>
            @if ($shouldShowNextRun() && $getNextRunDate())
                <div class="text-xs text-gray-500 dark:text-gray-400">
                    <span class="font-semibold">{{ __('cron-builder::cron-builder.next_run') }}:</span>
                    {{ $getNextRunDate() }}
                </div>
            @endif
        </div>
    </div>
</x-dynamic-component>
