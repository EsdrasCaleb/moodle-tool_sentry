<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Lang Package
 *
 * @package    tool_sentry
 * @author     Esdras Caleb <esdrascaleb@gmail.com>
 * @copyright  2023 Esdras Caleb
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Sentry report tool';
$string['pluginsettigs'] = "Sentry Configuration";
$string['options'] = "Options";
$string['options_desc'] = "This are need in order to work";
$string['sentry_options'] = "Sentry Options";
$string['sentry_options_desc'] = "This Change the way the options are sent to sentry";
$string['activate'] = "Activate Sentry Plugin";
$string['activate_desc'] = "Activate Sentry to send information to the configured dns";
$string['dns'] = "Sentry Server DNS";
$string['dns_desc'] = "Sentry server address with auth token";
$string['privacy:metadata'] = 'The  plugin does not store any personal data. However, it send the IP of a user that had an error to the sentry server configured in it.';
$string['release'] = "Sentry Release";
$string['release_desc'] = "Sets the release. If not set, the SDK will try to automatically configure a release out of the box but it's a better idea to manually set it to guarantee that the release is in sync with your deploy integrations.";
$string['send_default_pii'] = "Send Default PII";
$string['send_default_pii_desc'] = "If this flag is enabled, certain personally identifiable information (PII) is added by active integrations. By default, no such data is sent.";
$string['sample_rate'] = "Sample Rate";
$string['sample_rate_desc'] = "Configures the sample rate for error events, in the range of 0.0 to 1.0. The default is 1.0, which means that 100% of error events will be sent. If set to 0.1, only 10% of error events will be sent. Events are picked randomly.";
$string['profiles_sample_rate'] = "Profile Sample Rate";
$string['profiles_sample_rate_desc'] = "For Profiling to work, you have to first enable Sentry’s tracing via traces_sample_rate (like in the example above). Read our tracing setup documentation to learn how to configure sampling. If you set your sample rate to 1.0, all transactions will be captured.";
$string['traces_sample_rate'] = "Traces Sample Rate";
$string['traces_sample_rate_desc'] = "A number between 0 and 1, controlling the percentage chance a given transaction will be sent to Sentry. (0 represents 0% while 1 represents 100%.) Applies equally to all transactions created in the app. Either this or traces_sampler must be defined to enable tracing.";
$string['max_breadcrumbs'] = "Max Breadcrumbs";
$string['max_breadcrumbs_desc'] = "This variable controls the total amount of breadcrumbs that should be captured. This defaults to 100, but you can set this to any number. However, you should be aware that Sentry has a maximum payload size and any events exceeding that payload size will be dropped.";
$string['max_request_body_size'] = "Max Request Body Size";
$string['max_request_body_size_desc'] = "This parameter controls whether integrations should capture HTTP request bodies. Please note that the Sentry server limits HTTP request body size. The server always enforces its size limit, regardless of how you configure this option.";
$string['never'] = "Request bodies are never sent.";
$string['small'] = "Only small request bodies will be captured (typically 4KB)";
$string['medium'] = "Medium and small requests will be captured (typically 10KB)";
$string['always'] = "The SDK will always capture the request body as long as Sentry can make sense of it.";
$string['enable_tracing'] = "Enable Tracing";
$string['enable_tracing_desc'] = "A boolean value, if true, transactions and trace data will be generated and captured. This will set the traces-sample-rate to the recommended default of 1.0 if traces-sample-rate is not defined. Note that traces-sample-rate and traces-sampler take precedence over this option.";
$string['attach_stacktrace'] = "Attach Stacktrace";
$string['attach_stacktrace_desc'] = "When enabled, stack traces are automatically attached to all messages logged. Stack traces are always attached to exceptions; however, when this option is set, stack traces are also sent with messages. This option, for instance, means that stack traces appear next to all log messages.";
$string['max_value_length'] = "Max Value Length";
$string['max_value_length_desc'] = "The number of characters after which the values containing text in the event payload will be truncated (defaults to 1024).";
$string['environment'] = "Sentry Environment";
$string['environment_desc'] = "Sets the environment. This string is freeform and set to production by default. A release can be associated with more than one environment to separate them in the UI (think staging vs production or similar).";
$string['error_types'] = "Error Types";
$string['error_types_desc'] = "Sets which errors are reported. It takes the same values as PHP's error_reporting configuration parameter. By default all types of errors are be reported (equivalent to E_ALL).";
$string['server_name'] = "Server Name";
$string['server_name_desc'] = "This option can be used to supply a \"server name\". When provided, the name of the server is sent along and persisted in the event. For many integrations, the server name actually corresponds to the device hostname, even in situations where the machine is not actually a server.";
$string['ignore_exceptions'] = "Ignore Exceptions";
$string['ignore_exceptions_desc'] = "A list of class names that matches exceptions that shouldn't be sent to Sentry. Checks whether the provided class name is of a given type or subtype.";
$string['ignore_transactions'] = "Ignore Transactions";
$string['ignore_transactions_desc'] = "A list of strings that match transaction names that shouldn't be sent to Sentry.";
$string['in_app_include'] = "In App Include";
$string['ignore_transactions_desc'] = "A list of string prefixes of module names that belong to the app. This option takes precedence over in-app-exclude.";
$string['in_app_exclude'] = "In App Exnclude";
$string['in_app_exclude_desc'] = "A list of string prefixes of module names that do not belong to the app, but rather to third-party packages. Modules considered not part of the app will be hidden from stack traces by default.";
