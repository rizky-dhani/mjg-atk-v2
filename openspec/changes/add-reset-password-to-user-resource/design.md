# Design: Reset Password Action

## Overview
The "Reset Password" feature will be implemented as a Filament table action within the `UserResource`. It leverages existing `User` model attributes and roles to ensure secure and efficient password management.

## Technical Details

### Action Implementation
- **Location:** `app/Filament/Resources/Users/UserResource.php` within the `table` method's `recordActions` (or `actions`).
- **Component:** `Filament\Tables\Actions\Action`.
- **Label:** "Reset Password".
- **Icon:** `heroicon-o-key` or `heroicon-o-arrow-path`.
- **Color:** `warning` or `danger` to indicate it's a sensitive action.

### Authorization
- **Visibility:** Use `->visible(fn () => auth()->user()->isSuperAdmin())`.
- **Constraint:** Only `Super Admin` can see and trigger this action.

### Action Logic
- **Confirmation:** Use `->requiresConfirmation()` and `->modalHeading('Reset Password')` with a descriptive message.
- **Execution:**
    - Update `password` to `Hash::make('Atk2025!')`.
    - Update `has_changed_password` to `false`.
    - Save the model.
- **Notification:** Use `Notification::make()->success()->title('Password has been reset to default.')` to inform the admin.

### Security Considerations
- The default password `Atk2025!` is hardcoded in the `User` model's `booted` method for new users, so we follow that convention.
- Forcing `has_changed_password` to `false` ensures the user cannot continue using the default password indefinitely.
- Restricting to `Super Admin` prevents regular `Admin` users from resetting each other's (or Super Admin's) passwords unless explicitly allowed by the system configuration.

## Alternatives Considered
- **Custom Password Field in Action Modal:** Would allow the admin to set a specific password. However, a default password + forced change is simpler and follows existing patterns in the project.
- **Policy-based Authorization:** We could add a `resetPassword` method to `UserPolicy`. This is cleaner if we want to reuse this logic elsewhere.
