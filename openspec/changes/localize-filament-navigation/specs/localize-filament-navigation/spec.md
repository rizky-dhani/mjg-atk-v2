## ADDED Requirements

### Requirement: Filament Navigation Localization
Filament resource navigation properties SHALL be localizable to support multiple languages, particularly Bahasa.

#### Scenario: Localized Navigation Group Display
Given that a Filament resource has a `navigationGroup` property,
When the application's locale is set to Bahasa,
Then the `navigationGroup` displayed in the Filament sidebar menu MUST be the Bahasa translation of the group name.

#### Scenario: Localized Navigation Parent Item Display
Given that a Filament resource has a `navigationParentItem` property,
When the application's locale is set to Bahasa,
Then the `navigationParentItem` displayed in the Filament sidebar menu MUST be the Bahasa translation of the parent item name.

### Requirement: Use of Laravel Translation Functions
Filament resource `navigationGroup` and `navigationParentItem` properties SHALL utilize Laravel's translation functions.

#### Scenario: `__` Helper Function Usage for Navigation Group
Given a Filament resource with a `navigationGroup` property,
When the resource is defined,
Then the `navigationGroup` property MUST be assigned a value using the `__('translation.key')` helper function.

#### Scenario: `__` Helper Function Usage for Navigation Parent Item
Given a Filament resource with a `navigationParentItem` property,
When the resource is defined,
Then the `navigationParentItem` property MUST be assigned a value using the `__('translation.key')` helper function.

### Requirement: Bahasa Translation File Presence
A Bahasa translation file for Filament navigation keys SHALL exist and contain corresponding translations.

#### Scenario: `filament.php` Translation File
Given that Filament navigation properties are localized,
When the application's locale is set to `id` (Bahasa Indonesia),
Then a language file named `lang/id/filament.php` MUST exist.
And this file MUST contain key-value pairs for all `navigationGroup` and `navigationParentItem` strings used in Filament resources.
