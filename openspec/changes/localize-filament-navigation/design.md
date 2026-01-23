# Design: Localize Filament Navigation

## Architecture

This change will primarily affect the presentation layer of Filament resources. No core architectural changes to models, services, or controllers are anticipated. The existing Laravel localization mechanism will be leveraged.

### Data Flow

1.  **Resource Loading**: Filament resources are loaded during application bootstrap.
2.  **Navigation Property Resolution**: When Filament builds the navigation menu, it accesses the `navigationGroup` and `navigationParentItem` properties of each registered resource.
3.  **Translation Lookup**: By using `__('translation.key')`, Laravel's translation service will look up the corresponding string in the active locale's language files (e.g., `lang/id/filament.php`).
4.  **Menu Rendering**: The translated strings are then used to render the navigation menu in the Filament admin panel.

## Components Affected

-   **Filament Resource Classes**: (e.g., `app/Filament/Resources/UserResource.php`)
    -   `static ?string $navigationGroup = '...';`
    -   `static ?string $navigationParentItem = '...';`
-   **Language Files**: (e.g., `lang/id/filament.php`)
    -   New or updated keys for navigation groups and parent items.

## Implementation Details

-   **Translation Key Naming**: A consistent naming convention will be used for translation keys, e.g., `filament.navigation.group.<group_name>` and `filament.navigation.parent_item.<parent_item_name>`.
-   **Default Fallback**: Laravel's translation system automatically falls back to the default language (usually English) if a translation key is not found in the active locale, preventing broken navigation.

## Open Questions / Considerations

-   Are there any existing conventions for Filament-specific localization keys within the project? (Will assume a new `filament.php` file in `lang/id/` for now if it doesn't exist, or merge into an existing one if it does).
-   What are all the unique `navigationGroup` and `navigationParentItem` values currently used across all resources? (This will be determined during the task execution phase).
