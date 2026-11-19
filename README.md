# SENTRY
Sentry is a Open Source, Self-hosted and cloud-based application performance monitoring & error tracking that helps software teams see clearer, solve quicker, & learn continuously. Disponible in sentry.io.
# tool_sentry
Simple sentry plugin to moodle.

| Plugin version | Moodle | PHP |
| --- | --- | --- |
| 5.3.x | 5.3 | 8.3+ |
| 1.0.x | 4.x | 8.2+ (the bundled SDK dependencies require 8.2) |

## How Sentry is started

The plugin has two modes. The prepend mode wins when it is configured; otherwise the plugin works as before.

### 1. Plugin mode (default)

Configure the DSN and the options in *Site administration > Plugins > Admin tools > Sentry Configuration*.
The SDK is started by the plugin on the first Moodle event of each request, with the settings saved in the database.
Errors that happen before that point (for example "Database connection failed") are not sent.

### 2. Prepend mode (before Moodle)

PHP loads `admin/tool/sentry/prepend.php` before every script (web and CLI), so Sentry is started before
`config.php`, the database and the plugin settings. It is used only when **both** conditions are true:

1. `auto_prepend_file` points to the plugin's `prepend.php`;
2. the `SENTRY_DSN` environment variable is set.

When the prepend mode is active, the plugin does not start the SDK again, ignores the PHP options of the settings page
(only the Javascript loader is still used) and chains Sentry's handlers again after `lib/setup.php` (which replaces them
with Moodle's).

`php.ini` (or a file in `conf.d`, for example `30-sentry.ini`):

```ini
; Moodle 5.3 keeps the code in public/.
auto_prepend_file=/var/www/html/public/admin/tool/sentry/prepend.php
```

The prepend mode exists from version 5.3.0 (Moodle 5.3).

Environment variables (without `SENTRY_DSN` the prepend file does nothing):

| Variable | Default | Description |
| --- | --- | --- |
| `SENTRY_DSN` | | DSN of the Sentry project. Required. |
| `SENTRY_ENVIRONMENT` | `production` | Environment name. |
| `SENTRY_RELEASE` | | Release name, for example the image tag (`site@1.2.3`). |
| `SENTRY_SAMPLE_RATE` | `1.0` | Error sample rate, from 0.0 to 1.0. |
| `SENTRY_IGNORE_EXCEPTIONS` | `require_login_exception`, `require_login_session_timeout_exception`, `required_capability_exception`, `invalid_parameter_exception` | Comma separated exception classes; replaces the default list. |
| `SENTRY_SEND_USER_ID` | `false` | `true` sends only the numeric id of the Moodle user. |

Fixed options in this mode: request bodies are never sent, no default PII is sent, the values of sensitive
parameters (`token`, `wstoken`, `password`, `sesskey`, `key`, `secret`, `code`, `ticket`...) are replaced with
`[Filtered]`, and events are tagged with the Moodle component, the error code and the running task. The id of the
captured event is returned in the `X-Sentry-Event-Id` response header, which the web server can show on its error page.

The variables must reach PHP:

- **PHP-FPM**: `clear_env = no` in the pool, or `env[SENTRY_DSN] = ...` (or `fastcgi_param SENTRY_DSN ...` in nginx).
- **Apache (mod_php)**: `SetEnv SENTRY_DSN ...`.
- **Docker**: `environment:` of the PHP container.
- **CLI (cron)**: the variables of the shell that runs `admin/cli/cron.php`.

Optional: exceptions thrown inside `lib/setup.php` (for example, database down) reach Moodle's handler before the
plugin can chain the handlers. To capture them too, wrap the last line of `config.php`:

```php
try {
    require_once(__DIR__ . '/lib/setup.php');
} catch (\Throwable $e) {
    if (class_exists('\tool_sentry\local\prepend', false)) {
        \tool_sentry\local\prepend::capture($e);
    }
    throw $e;
}
```

To go back to the plugin mode, remove `auto_prepend_file` or unset `SENTRY_DSN`.

License
=======

GNU GPL v3 or later. http://www.gnu.org/copyleft/gpl.html
