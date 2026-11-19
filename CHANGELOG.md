# Changelog

## 1.1.0 - 2026-11-19

### Added
- Prepend mode: `admin/tool/sentry/prepend.php`, set in `auto_prepend_file`, starts Sentry before Moodle with the
  options of the `SENTRY_*` environment variables (`SENTRY_DSN`, `SENTRY_ENVIRONMENT`, `SENTRY_RELEASE`,
  `SENTRY_SAMPLE_RATE`, `SENTRY_IGNORE_EXCEPTIONS`, `SENTRY_SEND_USER_ID`). Errors that happen before the database and
  before the first Moodle event are now sent too.
- In prepend mode the plugin does not start the SDK again and chains Sentry's handlers after `lib/setup.php`
  (`after_config` hook, or the `tool_sentry_after_config()` callback before Moodle 4.4).
- Notice on the settings page when Sentry was started by the prepend file.
- PHPUnit tests for the choice of the mode.

### Unchanged
- Without `auto_prepend_file` pointing to the plugin or without `SENTRY_DSN`, the plugin works as before, with the
  settings saved in the administration pages.

## 1.0.3 - 2026-06-06

- Fix the origin of errors sent to Sentry and add the context of scheduled and adhoc tasks.
- Avoid the PHP 8.4 `E_STRICT` deprecation.
