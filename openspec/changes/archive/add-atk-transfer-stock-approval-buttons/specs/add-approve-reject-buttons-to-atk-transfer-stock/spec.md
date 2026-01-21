## ADDED Requirements

### Requirement: Implement Approve/Reject Buttons for AtkTransferStock
This requirement SHALL integrate "Approve" and "Reject" buttons into the Filament admin panel for `AtkTransferStock` records, leveraging the existing approval flow.

#### Scenario: Authorized user approves a pending AtkTransferStock

Given a user is authenticated and authorized to approve `AtkTransferStock` requests,
And an `AtkTransferStock` record exists with a status of 'pending approval',
When the user views the `AtkTransferStock` detail page in Filament,
And the user clicks the "Approve" button,
Then the existing approval flow logic for `AtkTransferStock` MUST be triggered with an 'approved' status,
And the `AtkTransferStock` record's status MUST be updated to 'approved' in the database,
And a confirmation notification SHOULD be displayed to the user.

#### Scenario: Authorized user rejects a pending AtkTransferStock

Given a user is authenticated and authorized to reject `AtkTransferStock` requests,
And an `AtkTransferStock` record exists with a status of 'pending approval',
When the user views the `AtkTransferStock` detail page in Filament,
And the user clicks the "Reject" button,
Then the existing approval flow logic for `AtkTransferStock` MUST be triggered with a 'rejected' status,
And the `AtkTransferStock` record's status MUST be updated to 'rejected' in the database,
And a confirmation notification SHOULD be displayed to the user.

#### Scenario: Unauthorized user does not see Approve/Reject buttons

Given a user is authenticated but NOT authorized to approve or reject `AtkTransferStock` requests,
When the user views an `AtkTransferStock` detail page in Filament,
Then the "Approve" and "Reject" buttons MUST NOT be visible to the user.

#### Scenario: Approve/Reject buttons are not visible for non-pending requests

Given a user is authorized to approve/reject `AtkTransferStock` requests,
And an `AtkTransferStock` record exists with a status other than 'pending approval' (e.g., 'approved', 'rejected'),
When the user views the `AtkTransferStock` detail page in Filament,
Then the "Approve" and "Reject" buttons MUST NOT be visible to the user.
