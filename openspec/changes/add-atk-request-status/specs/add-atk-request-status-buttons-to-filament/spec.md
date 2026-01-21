## ADDED Requirements

### Requirement: Implement Draft and Publish Functionality in Filament
This requirement SHALL integrate "Save to Draft" and "Publish" buttons into the Filament admin panel for ATK Stock Requests, controlling the request's `status`.

#### Scenario: User saves a new ATK Stock Request as draft

Given a user is a division administrator and is on the ATK Stock Request creation page,
When the user fills out the form and clicks "Save to Draft",
Then a new `AtkStockRequest` record MUST be created with its `status` set to `'draft'`.

#### Scenario: User publishes a new ATK Stock Request

Given a user is a division administrator and is on the ATK Stock Request creation page,
When the user fills out the form and clicks "Publish",
Then a new `AtkStockRequest` record MUST be created with its `status` set to `'published'`.

#### Scenario: User updates an existing ATK Stock Request and saves as draft

Given a user is a division administrator and is on the ATK Stock Request edit page for an existing request,
When the user modifies the form and clicks "Save to Draft",
Then the existing `AtkStockRequest` record's `status` MUST be updated to `'draft'`.

#### Scenario: User updates an existing ATK Stock Request and publishes

Given a user is a division administrator and is on the ATK Stock Request edit page for an existing request,
When the user modifies the form and clicks "Publish",
Then the existing `AtkStockRequest` record's `status` MUST be updated to `'published'`.

#### Scenario: Division admin publishes a draft request from the list page

Given a user is a division administrator and is on the ATK Stock Request list page,
And there is an existing `AtkStockRequest` with `status` `'draft'`,
When the user selects the "Publish" action for that request,
Then the `AtkStockRequest` record's `status` MUST be updated to `'published'`.

#### Scenario: Division admin unpublishes a published request from the list page

Given a user is a division administrator and is on the ATK Stock Request list page,
And there is an existing `AtkStockRequest` with `status` `'published'`,
When the user selects the "Unpublish" action for that request,
Then the `AtkStockRequest` record's `status` MUST be updated to `'draft'`.

#### Scenario: Non-division admin cannot see status modification buttons/actions

Given a user is not a division administrator,
When the user views the ATK Stock Request creation, edit, or list pages,
Then the "Save to Draft", "Publish", and "Unpublish" buttons/actions MUST NOT be visible or accessible to them.

#### Scenario: ATK Stock Request list page includes status filtering

Given a user is on the ATK Stock Request list page,
When the user views the filters,
Then there MUST be an option to filter requests by their `status` (`'draft'` or `'published'`).

#### Scenario: Approval flow only sees and processes published requests

Given an `AtkStockRequest` exists with a `status` of `'draft'`,
When an approval flow attempts to view or process this request,
Then the `AtkStockRequest` MUST NOT be visible or accessible to the approval flow.

Given an `AtkStockRequest` exists with a `status` of `'published'`,
When an approval flow attempts to view or process this request,
Then the `AtkStockRequest` MUST be visible and accessible to the approval flow for approval or rejection.