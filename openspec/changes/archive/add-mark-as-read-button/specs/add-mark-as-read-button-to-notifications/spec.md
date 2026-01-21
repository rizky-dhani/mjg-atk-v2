## ADDED Requirements

### Requirement: Add "Mark as Read" Button for Individual Notifications
This requirement SHALL implement a "Mark as Read" button or action for individual notifications within the Filament admin panel's notifications panel.

#### Scenario: User marks a single notification as read

Given a user is authenticated and has unread notifications,
When the user views the notifications panel,
And the user clicks the "Mark as Read" button/action for a specific unread notification,
Then that specific notification MUST be marked as read in the database,
And the notification's visual state in the UI SHOULD update to reflect its read status.

#### Scenario: User marks all notifications as read

Given a user is authenticated and has unread notifications,
When the user views the notifications panel,
And the user clicks the "Mark All as Read" button/action,
Then all unread notifications for that user MUST be marked as read in the database,
And the notifications panel UI SHOULD update to reflect that all notifications are now read.
