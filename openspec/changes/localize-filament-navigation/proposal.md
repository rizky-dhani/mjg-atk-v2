# Proposal: Localize Filament Navigation

This proposal addresses the need to localize the `navigationGroup` and `navigationParentItem` properties of Filament resources to support Bahasa. Currently, these properties may use hardcoded English strings, which can lead to an inconsistent user experience in a localized application.

## Motivation

To provide a fully localized user interface, all user-facing strings, including navigation elements in Filament, should be translatable. By localizing `navigationGroup` and `navigationParentItem`, we ensure that the Filament navigation menus display in Bahasa, enhancing the user experience for Bahasa-speaking users.

## High-Level Design

The change will involve:
- Identifying Filament resources that utilize `navigationGroup` and/or `navigationParentItem`.
- Modifying these resources to use Laravel's translation functions (e.g., `__('translation.key')`) for these navigation properties.
- Ensuring that corresponding translation keys are present in the Bahasa language files (`lang/id/`).

## Security Considerations

This change is primarily focused on localization and does not introduce any new security risks. It improves usability and accessibility for a wider audience.

## Impact

- **Improved User Experience**: Filament navigation will be displayed in Bahasa.
- **Consistency**: Aligns with the overall localization efforts of the application.
- **Maintainability**: Centralizes navigation strings in language files, making future updates easier.