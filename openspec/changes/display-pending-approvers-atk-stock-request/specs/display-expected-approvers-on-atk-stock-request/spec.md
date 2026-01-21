## ADDED Requirements

### Requirement: Display Approver Name and Position in AtkStockRequest View Page
This requirement SHALL ensure that the name and position of each expected approver in the associated `Approval Flow` are displayed on the `AtkStockRequest` View page, irrespective of whether they have approved/rejected the request.

#### Scenario: Expected approvers are visible on a pending AtkStockRequest

Given a user views an `AtkStockRequest` that is currently in a 'pending approval' state,
When the `AtkStockRequest` View page is displayed,
Then a section MUST be visible showing the list of approvers defined in the associated `Approval Flow`,
And for each approver, their name and position MUST be displayed.

#### Scenario: Expected approvers are visible on an approved AtkStockRequest

Given a user views an `AtkStockRequest` that has been 'approved',
When the `AtkStockRequest` View page is displayed,
Then a section MUST be visible showing the list of approvers defined in the associated `Approval Flow`,
And for each approver, their name and position MUST be displayed.
