# Design Document: Set System Default Language to Bahasa Indonesia

## Overview

This document describes the design for configuring the application to use Bahasa Indonesia as its default language. The goal is to provide a localized user experience for Indonesian speakers, ensuring all default text and messages are presented in Bahasa.

## Configuration Changes

The primary change involves updating the Laravel application's locale settings.

*   **`config/app.php`**: The `locale` and `fallback_locale` configuration values will be set to `id` (the ISO 639-1 code for Indonesian) within the `config/app.php` file. This is the central point for Laravel's localization settings.

    ```php
    'locale' => 'id',
    'fallback_locale' => 'id',
    ```

## Localization Files

For the application to display content in Bahasa, corresponding translation files must be present and adequately populated.

*   **`resources/lang/id.json`**: This file will contain short, single-line translations, typically used for JavaScript-driven localization or simple UI strings.
*   **`resources/lang/id/*.php`**: This directory will contain PHP array-based translation files, organized by feature or domain (e.g., `auth.php`, `pagination.php`, `validation.php`, `custom.php`). These are commonly used for more complex messages and Laravel's built-in components.
*   **Package Translations**: For third-party packages like Filament, their respective translation files for Bahasa Indonesia must be present and correctly configured. Laravel's package auto-discovery typically handles this, but custom translations for package strings may be required if default ones are insufficient.

## User Interface Considerations

*   **Filament Panel**: All Filament UI components, including navigation, forms, tables, and notifications, should reflect the Bahasa localization. This requires ensuring that Filament's own translation files for Bahasa are installed and active.
*   **Custom UI**: Any custom blade templates or frontend components that use Laravel's translation functions (`__('key')` or `@lang('key')`) will automatically pick up the new default language, provided the translation keys exist in the `id` localization files.

## Implementation Details

*   **Configuration Update**: A straightforward modification to `config/app.php`.
*   **Translation File Audit**: A thorough review of existing translation keys and an effort to create or adapt Bahasa translations for all user-facing strings will be necessary. This may involve using `php artisan lang:publish` for vendor translations or manually creating files.
*   **Testing Locale**: Ensure that the application can correctly switch to and display content in Bahasa.

## Future Considerations

*   User-selectable language options (if multiple languages are eventually supported).
*   Automatic language detection based on browser settings.
*   Integration with a translation management system.
