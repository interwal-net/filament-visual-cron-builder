# Changelog

All notable changes to `filament-visual-cron-builder` are documented here.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.2.0] - 2026-07-07

### Added

- Multiple ranges per position (`1-5,10-12`) - a new range row appears as you
  fill the last one; internal range state is now `ranges: [{from, to}, ...]`
  (the legacy single `from`/`to` shape still composes).
- Tabs layout - `->layout('tabs')` or the `cron-builder.layout` config key
  renders one position at a time behind a tab bar. Optional live token badges
  in the tab headers via `->showTabTokens()` or `cron-builder.show_tab_tokens`
  (off by default).

### Changed

- Specific values are now toggle chips instead of a native multi-select - no
  more Ctrl/Cmd-click to pick multiple values.
- Modes render as a segmented control instead of a select.
- The field ships its styles as a plain CSS file registered through
  `FilamentAsset`. No Tailwind build is required and the custom-theme
  `@source` step is gone.
- Specific value lists compose sorted and de-duplicated (`4,5,12,20`).

### Fixed

- Livewire DOM-diff mix-ups between columns when switching modes or adding
  ranges (missing `wire:key` on dynamic blocks).

## [1.1.1] - 2026-07-07

### Changed

- Dropped Laravel 11 support (`illuminate/contracts: ^12.0 || ^13.0`). Laravel 11
  reached end of security support in March 2026 and every 11.x release carries
  open security advisories, which blocks installation under Composer's default
  advisory policy.

## [1.1.0] - 2026-07-07

### Added

- Filament v5 / Livewire v4 support, alongside existing Filament v4 / Livewire v3
  (`filament/forms: ^4.0 || ^5.0`). No code changes were needed - the field has
  no JS bundle and the Filament v5 field API is unchanged.
- Laravel 13 support (`illuminate/contracts: ^11.28 || ^12.0 || ^13.0`; requires
  PHP 8.3+ on Laravel 13).
- Workbench demo panel (`composer serve`) with a `ScheduleResource` and seeded
  sample expressions, for manual verification and screenshots. Dev-only.
- CI now tests the full PHP x Laravel x Filament matrix.
- `->live()` overrides (`onBlur`, `debounce`, `condition: false`) - the field
  is live by default, but the binding modifiers are no longer hard-coded in
  the view.
- `->afterStateUpdated()` fires when any column of the builder changes.

### Changed

- Minimum `illuminate/contracts` raised from `^11.0` to `^11.28` (Filament's own
  floor; no practical impact).

## [1.0.0] - 2026-06-22

### Added

- `CronBuilder` form field for Filament v4 - a drop-in replacement for a
  `TextInput` bound to a cron column; saved state is a standard 5-field cron string.
- 5 columns (minute, hour, day-of-month, month, day-of-week), each with
  `Every` / `Specific` / `Range` / `Step` modes.
- Live human-readable preview, raw expression, and optional next-run date
  (`->showNextRun()`).
- `CronExpressionBuilder` support class - `compose`/`parse`/`parseField`/
  `composeField`/`humanReadable`/`isValid`, with stable round-tripping.
- `ValidCronExpression` validation rule.
- English translations, publishable config and views.

[Unreleased]: https://github.com/interwal-net/filament-visual-cron-builder/compare/v1.2.0...HEAD
[1.2.0]: https://github.com/interwal-net/filament-visual-cron-builder/compare/v1.1.1...v1.2.0
[1.1.1]: https://github.com/interwal-net/filament-visual-cron-builder/compare/v1.1.0...v1.1.1
[1.1.0]: https://github.com/interwal-net/filament-visual-cron-builder/compare/v1.0.0...v1.1.0
[1.0.0]: https://github.com/interwal-net/filament-visual-cron-builder/releases/tag/v1.0.0
