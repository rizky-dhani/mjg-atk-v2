# Proposal: Add Reset Password Action to User Resource

## Problem
Currently, there is no quick way for a Super Admin to reset a user's password if they forget it. Administrators have to manually edit the user record and provide a new password, or it might not even be easily accessible if the password field is not in the Edit form (it isn't in `UserResource`).

## Proposed Solution
Add a "Reset Password" action to the `UserResource` table. This action will:
- Be visible ONLY to users with the `Super Admin` role.
- Reset the user's password to a default value (`Atk2025!`).
- Set the `has_changed_password` flag to `false` to force the user to change their password upon next login.
- Provide a confirmation modal before proceeding.
- Send a success notification upon completion.

Additionally, update the password change flow to:
- Redirect the user immediately to the dashboard after they successfully change their forced password.

## Goals
- Allow Super Admins to quickly reset forgotten passwords.
- Maintain security by forcing users to change the default password immediately.
- Restrict this powerful action to the highest level of authority.

## Non-Goals
- Implement a "Forgot Password" self-service flow (emails, tokens, etc.).
- Allow custom password setting during the reset action.
