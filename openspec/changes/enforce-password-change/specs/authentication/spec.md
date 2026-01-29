# Spec Delta: Password Change Enforcement

## ADDED Requirements

### Requirement: Users MUST change default passwords upon first login
The system MUST detect if a user is using a default generated password and prevent them from accessing dashboard features until the password has been updated.

#### Scenario: Redirect to profile page
- **Given** I am a user with a default password (indicated by `has_changed_password` being `false`)
- **When** I attempt to access the dashboard home or any resource
- **Then** I MUST be redirected to the profile page
- **And** I MUST see a notice informing me to secure my account by changing my password

#### Scenario: Successful password update unlocks dashboard
- **Given** I am on the profile page because I am forced to change my password
- **When** I successfully update my password
- **Then** the `has_changed_password` flag MUST be set to `true`
- **And** I MUST be able to access all other parts of the dashboard
