## ADDED Requirements

### Requirement: Bulk Export ATK Stock Requests
This requirement SHALL outline the process for exporting multiple ATK Stock Requests as a bulk action from the Filament admin panel.

#### Scenario: User exports multiple ATK Stock Requests

Given a user is authenticated and authorized to view ATK Stock Requests,
When the user navigates to the ATK Stock Request listing page in the Filament admin panel,
And the user selects multiple ATK Stock Requests,
And the user selects the "Bulk Export" bulk action,
Then a CSV file containing the combined details of all selected ATK Stock Requests and their associated items should be generated and downloaded.
If the number of selected requests is large, the export process should be queued and the user notified upon completion.
