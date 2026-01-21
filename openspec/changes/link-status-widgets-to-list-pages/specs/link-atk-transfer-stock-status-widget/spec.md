## ADDED Requirements

### Requirement: Link AtkTransferStockStatus Widget to Filtered List Page
This requirement SHALL ensure that the `AtkTransferStockStatus` widget navigates to the `AtkTransferStocks` list page with the corresponding status filter applied when clicked.

#### Scenario: Clicking a rejected transfers widget redirects to filtered list

Given a user is on the dashboard with an `AtkTransferStockStatus` widget showing "Rejected Transfers",
When the user clicks on this widget,
Then the user MUST be redirected to the `AtkTransferStocks` list page,
And the list page MUST be filtered to show only transfers with a 'rejected' status.
