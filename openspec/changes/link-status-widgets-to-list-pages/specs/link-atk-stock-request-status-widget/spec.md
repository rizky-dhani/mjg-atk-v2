## ADDED Requirements

### Requirement: Link AtkStockRequestStatus Widget to Filtered List Page
This requirement SHALL ensure that the `AtkStockRequestStatus` widget navigates to the `AtkStockRequests` list page with the corresponding status filter applied when clicked.

#### Scenario: Clicking a pending requests widget redirects to filtered list

Given a user is on the dashboard with an `AtkStockRequestStatus` widget showing "Pending Requests",
When the user clicks on this widget,
Then the user MUST be redirected to the `AtkStockRequests` list page,
And the list page MUST be filtered to show only requests with a 'pending' status.
