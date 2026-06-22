# Changelog

All notable changes to `filament-visual-cron-builder` are documented here.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

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

[Unreleased]: https://github.com/interwal-net/filament-visual-cron-builder/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/interwal-net/filament-visual-cron-builder/releases/tag/v1.0.0
