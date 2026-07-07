@php
    use InterwalNet\CronBuilder\Support\CronExpressionBuilder as Builder;

    $statePath = $getStatePath();
    $columns = $getCronColumns();
    $modeOptions = $getModeOptions();
    $positionLabels = $getPositionLabels();
    $wireModel = $applyStateBindingModifiers('wire:model');
    $layout = $getLayout();

    $chipCols = [
        'minute' => 6,
        'hour' => 6,
        'day' => 7,
        'month' => 3,
        'weekday' => 4,
    ];
@endphp

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div class="cb-root fi-cron-builder">
        @if ($layout === 'tabs')
            <div class="cb-tabs" x-data="{ tab: @js(array_key_first($positionLabels)) }">
                <div class="cb-tabbar" role="tablist">
                    @foreach ($positionLabels as $key => $label)
                        <button
                            type="button"
                            class="cb-tab"
                            role="tab"
                            x-bind:class="{ 'cb-tab--active': tab === @js($key) }"
                            x-on:click="tab = @js($key)"
                        >
                            {{ $label }}
                            <span class="cb-tab-token">{{ Builder::composeField($columns[$key]) }}</span>
                        </button>
                    @endforeach
                </div>

                @foreach ($positionLabels as $key => $label)
                    @php($column = $columns[$key])
                    <div
                        class="cb-card cb-panel"
                        role="tabpanel"
                        x-show="tab === @js($key)"
                        x-cloak
                        wire:key="{{ $statePath }}.{{ $key }}"
                    >
                        @include('cron-builder::partials.column', ['showLabel' => false])
                    </div>
                @endforeach
            </div>
        @else
            <div class="cb-columns">
                @foreach ($positionLabels as $key => $label)
                    @php($column = $columns[$key])
                    <div class="cb-card" wire:key="{{ $statePath }}.{{ $key }}">
                        @include('cron-builder::partials.column', ['showLabel' => true])
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Live preview --}}
        <div class="cb-preview">
            <div class="cb-preview-text">{{ $getHumanReadable() }}</div>
            <div class="cb-preview-meta">
                <span class="cb-expression">{{ $getComposedExpression() }}</span>
                @if ($shouldShowNextRun() && $getNextRunDate())
                    <span class="cb-next-run">
                        {{ __('cron-builder::cron-builder.next_run') }}: {{ $getNextRunDate() }}
                    </span>
                @endif
            </div>
        </div>
    </div>
</x-dynamic-component>
