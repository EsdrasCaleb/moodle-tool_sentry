# Changelog

## 1.0.4 - 2026-11-19

### Fixed
- PHP 8.1: the bundled `vendor/` required PHP 8.2 (`symfony/options-resolver` 7.4), and Composer's platform check
  threw "Your Composer dependencies require a PHP version >= 8.2.0" on every page that loaded the plugin, including
  the installation. `composer.json` now sets `config.platform.php` to 8.1.0 and `symfony/options-resolver` was
  downgraded to 6.4. The Sentry SDK is unchanged (4.27.0).

### Added
- PHPUnit test that loads the bundled SDK, so CI installs the test site and catches platform errors.
- Automatic release on `vX.Y.Z` tags: GitHub Release with the ZIP and upload to the Moodle Marketplace.

## 1.0.3 - 2026-06-06

- Fix the origin of errors sent to Sentry and add the context of scheduled and adhoc tasks.
- Avoid the PHP 8.4 `E_STRICT` deprecation.
