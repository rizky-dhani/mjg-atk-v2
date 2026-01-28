# Specification: User Multi-Division

## MODIFIED Requirements

### Requirement: User belongs to multiple divisions
Users SHALL be assigned to more than one division.

#### Scenario: Assign multiple divisions to a user
- Given a user "John Doe"
- When I assign him to "Division A" and "Division B"
- Then the system should store both assignments in the `division_user` table.

#### Scenario: Retrieve user's divisions
- Given a user "John Doe" assigned to "Division A" and "Division B"
- When I access his divisions relationship
- Then I should receive both "Division A" and "Division B".

### Requirement: Default password generation
Users created without a password MUST be assigned a default password.

#### Scenario: User created without password
- Given I am creating a new user "John Doe"
- And I do not provide a password
- When the user is saved
- Then the system MUST set the password to a default value (e.g., 'password').
