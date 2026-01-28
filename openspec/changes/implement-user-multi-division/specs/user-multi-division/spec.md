# Specification: User Multi-Division

## Requirements

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
