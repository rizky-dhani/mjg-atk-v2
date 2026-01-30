# Specification: Budgeting Authorization

## ADDED Requirements

### Requirement: Role-based access to AtkBudgeting
The system SHALL restrict management of `AtkBudgeting` records to users with `Admin` or `Super Admin` roles.

#### Scenario: Unauthorized user access
- Given a user "Staff User" with only "Staff" role
- When I attempt to access the AtkBudgeting resource
- Then the system should deny access.

#### Scenario: Admin user access
- Given a user "Admin User" with "Admin" role
- When I access the AtkBudgeting resource
- Then the system should allow access.

### Requirement: Division-based access to AtkBudgeting
Users with `Admin` role SHALL only be allowed to create, update, or delete `AtkBudgeting` records for divisions they belong to.

#### Scenario: Admin managing budget for their own division
- Given a user "Admin User" assigned to "Division A"
- When I attempt to edit a budget record for "Division A"
- Then the system should allow the update.

#### Scenario: Admin managing budget for another division
- Given a user "Admin User" assigned to "Division A"
- When I attempt to edit a budget record for "Division B"
- Then the system should deny the update.

### Requirement: Automated division_id assignment
The system SHALL automate the `division_id` assignment for new `AtkBudgeting` records based on the logged-in user's assigned divisions.

#### Scenario: Automatic division assignment for single-division user
- Given a user "Admin User" assigned ONLY to "Division A"
- When I create a new budget record
- Then the system should automatically assign "Division A" to the record.

#### Scenario: Restricted division selection for multi-division user
- Given a user "Admin User" assigned to "Division A" and "Division B"
- When I create a new budget record
- Then the system should only allow me to select between "Division A" and "Division B".
