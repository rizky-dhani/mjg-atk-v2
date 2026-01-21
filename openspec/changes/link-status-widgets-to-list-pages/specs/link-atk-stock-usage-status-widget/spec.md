## ADDED Requirements

### Requirement: Link AtkStockUsageStatus Widget to Filtered List Page
This requirement SHALL ensure that the `AtkStockUsageStatus` widget navigates to the `AtkStockUsages` list page with the corresponding status filter applied when clicked.

#### Scenario: Clicking a completed usages widget redirects to filtered list

Given a user is on the dashboard with an `AtkStockUsageStatus` widget showing "Completed Usages",
When the user clicks on this widget,
Then the user MUST be redirected to the `AtkStockUsages` list page,
And the list page MUST be filtered to show only usages with a 'completed' status.
