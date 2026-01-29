# Specification: Approval Flow Pinning

## ADDED Requirements

### Requirement: Assign specific user to approval step
An `ApprovalFlowStep` MUST support assignment to a specific user.

#### Scenario: Assign user to step
- Given an `ApprovalFlowStep` for "Division Head"
- When I select user "Jane Smith" as the specific approver
- Then only "Jane Smith" should be allowed to approve this step, even if other users have the "Head" role.

#### Scenario: Fallback to role and division
- Given an `ApprovalFlowStep` with no specific user assigned
- When a request is submitted
- Then any user with the matching role and division should be allowed to approve.

### Requirement: Validate specific approver
The system SHALL ensure that only the pinned user can approve if `user_id` is set.

#### Scenario: Deny approval to non-pinned user
- Given an `ApprovalFlowStep` pinned to "Jane Smith"
- When "John Doe" (who has the same role and division) attempts to approve
- Then the system should deny the approval.
