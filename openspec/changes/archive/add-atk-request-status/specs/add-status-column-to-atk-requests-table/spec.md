## ADDED Requirements

### Requirement: Add Status Column to ATK Stock Requests Table
This requirement SHALL ensure that the `atk_stock_requests` table includes a new column to manage the workflow status of each request.

#### Scenario: Database schema updated with status column

Given the application's database,
When a migration is run to update the `atk_stock_requests` table,
Then the `atk_stock_requests` table MUST have a new `status` column of type `ENUM('draft', 'published')`,
And the `status` column MUST have a default value of `'draft'`.
