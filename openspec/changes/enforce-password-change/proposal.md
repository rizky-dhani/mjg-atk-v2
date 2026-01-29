# Proposal: Enforce Password Change from Default

## Problem
New users are created with a default password (`Atk2025!`). Using a default password is a security risk. Users should be forced to change their password upon their first login before they can access any other part of the system.

## Proposed Solution
1. Add a `has_changed_password` boolean column to the `users` table, defaulting to `false`.
2. Implement a custom Filament profile page that updates this flag to `true` when the password is successfully changed.
3. Create a middleware `CheckPasswordChanged` that redirects users to the profile page if `has_changed_password` is `false`.
4. Register this middleware in the Filament `DashboardPanelProvider`.
5. Display a prominent notice on the profile page for users who haven't changed their password.

## Expected Impact
- Improved security by ensuring no user stays on a default password.
- Better user awareness of account security.
- Minimal disruption, as it only affects users who haven't secured their accounts yet.
