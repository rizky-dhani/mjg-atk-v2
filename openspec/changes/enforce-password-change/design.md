# Design: Enforce Password Change

## Overview
The solution involves database changes, middleware for redirection, and UI enhancements in Filament.

## Database Changes
A new column `has_changed_password` will be added to the `users` table.
- Type: `boolean`
- Default: `false`
- Existing users: Will be initialized to `true` to avoid forcing all existing 46 users to change passwords simultaneously, unless they are specifically identified as needing a change. However, for a clean start, we could also default them to `false` and let them change once. *Decision:* Default to `false` for all to ensure security across the board.

## Middleware: `CheckPasswordChanged`
This middleware will execute on every request within the Filament panel.
- Logic:
    - If user is logged in AND `!$user->has_changed_password`:
        - If the current route is NOT the profile page AND NOT a logout route:
            - Redirect to the profile page with a warning notification.

## Custom Profile Page
We will extend `Filament\Auth\Pages\EditProfile`.
- Customizations:
    - Add a `Section` or `Notice` component at the top of the form if `!$user->has_changed_password`.
    - Override the `handleRegistration` or `afterSave` logic to set `has_changed_password = true`.
    - Ensure that the password change field is either mandatory or clearly emphasized.

## User Flow
1. User logs in with default password.
2. Middleware detects `has_changed_password == false`.
3. User is redirected to `/dashboard/profile`.
4. User sees a notice: "Please change your default password to secure your account."
5. User changes password and saves.
6. `has_changed_password` becomes `true`.
7. User can now navigate to other pages.
