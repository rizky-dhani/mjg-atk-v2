# Spec Delta: Authentication

## MODIFIED Requirements

### Requirement: Password Change Enforcement
The system MUST detect if a user is using a default generated password and prevent them from accessing dashboard features until the password has been updated. Upon successful password update, the user MUST be redirected to the dashboard.

#### Scenario: Redirect to profile page
- **Given** I am a user with a default password (indicated by `has_changed_password` being `false`)
- **When** I attempt to access the dashboard home or any resource
- **Then** I MUST be redirected to the profile page
- **And** I MUST see a notice informing me to secure my account by changing my password

#### Scenario: Successful password update redirects to dashboard
- **Given** I am on the profile page because I am forced to change my password
- **When** I successfully update my password
- **Then** the `has_changed_password` flag MUST be set to `true`
- **And** I MUST be redirected immediately to the dashboard home page
- **And** I MUST see a success notification
