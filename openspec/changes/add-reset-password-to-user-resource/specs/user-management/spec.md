# Spec Delta: User Management

## ADDED Requirements

### Requirement: Reset Password Action
The system SHALL provide a way for Super Admins to reset a user's password to a default value and force a password change upon next login.

#### Scenario: Successful password reset by Super Admin
- **WHEN** a Super Admin triggers the "Reset Password" action for a user and confirms it
- **THEN** the user's password MUST be updated to `Atk2025!`
- **AND** the user's `has_changed_password` flag MUST be set to `false`
- **AND** a success notification MUST be displayed

#### Scenario: Access restriction for Reset Password action
- **WHEN** a user without the `Super Admin` role views the user list or attempts to trigger the reset action
- **THEN** the action MUST NOT be visible
- **AND** any attempt to execute it MUST be forbidden