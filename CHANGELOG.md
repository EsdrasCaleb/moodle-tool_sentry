# Changelog

## 5.3.0 - 2026-11-19

New version line for **Moodle 5.3** only (`requires` 5.3, `supported` [503, 503], PHP 8.3+). Sites on Moodle 4.x keep
using the 1.0.x line, which is unchanged.

### Added
- Prepend mode: `admin/tool/sentry/prepend.php` (`public/admin/tool/sentry/prepend.php` in the Moodle tree), set in
  `auto_prepend_file`, starts Sentry before Moodle with the options of the `SENTRY_*` environment variables
  (`SENTRY_DSN`, `SENTRY_ENVIRONMENT`, `SENTRY_RELEASE`, `SENTRY_SAMPLE_RATE`, `SENTRY_IGNORE_EXCEPTIONS`,
  `SENTRY_SEND_USER_ID`). Errors that happen before the database and before the first Moodle event are now sent too.
- In prepend mode the plugin does not start the SDK again and chains Sentry's handlers after `lib/setup.php`
  (`core\hook\after_config`).
- Notice on the settings page when Sentry was started by the prepend file.
- PHPUnit tests for the choice of the mode.

### Changed
- Sentry PHP SDK 4.27.0 → 4.38.0. `composer.json` sets `config.platform.php` to 8.3.0, the minimum of Moodle 5.3.
- CI: Moodle 5.3 with PHP 8.3 and 8.4, PostgreSQL 17 and MariaDB 11.4.

### Unchanged
- Without `auto_prepend_file` pointing to the plugin or without `SENTRY_DSN`, the plugin works as before, with the
  settings saved in the administration pages.

## 1.0.3 - 2026-06-06

- Fix the origin of errors sent to Sentry and add the context of scheduled and adhoc tasks.
- Avoid the PHP 8.4 `E_STRICT` deprecation.
