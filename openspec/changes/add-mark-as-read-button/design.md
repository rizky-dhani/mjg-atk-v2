# Design Document: "Mark as Read" Button for Notifications Panel

## Overview

This document describes the design for adding a "Mark as Read" button to the notifications panel within the Filament admin panel. The primary goal is to provide users with a convenient way to manage their unread notifications, thereby improving the overall user experience and clarity of the notification system.

## User Interface Integration (Filament)

The "Mark as Read" functionality will be integrated into the existing Filament notifications panel.

### Individual Notification

A "Mark as Read" button or action will be added for each individual notification entry. This will typically appear as an icon (e.g., an eye icon or a checkmark) or a small text button within the notification's display. Clicking this will mark only that specific notification as read.

### Mark All as Read (Optional/Future Consideration)

Depending on the design of the notifications panel, a "Mark All as Read" button could be added at a prominent location (e.g., at the top of the panel). This would mark all currently visible unread notifications for the user as read. For the initial implementation, the focus will be on individual notification marking.

## Backend Logic

When the "Mark as Read" action is triggered for a notification, the backend will update the corresponding notification record in the database.

*   The `read_at` timestamp column in the `notifications` table (or equivalent) will be set to the current timestamp for the specified notification(s).
*   For "Mark All as Read", all unread notifications belonging to the authenticated user will have their `read_at` timestamp updated.

## Implementation Details

*   **Filament Actions**: A custom Filament Action will be created for the "Mark as Read" functionality. This action will invoke a Livewire component method or a controller action to handle the backend logic.
*   **Database Interaction**: Eloquent will be used to update the `read_at` timestamp on the `Notification` model (or the relevant model handling notifications).
*   **Real-time Updates**: If the notifications panel supports real-time updates (e.g., via Livewire polling or broadcasting), the UI should automatically reflect the change in notification status (e.g., disappearing from the unread list, changing visual style).

## Future Considerations

*   "Mark All as Read" functionality.
*   Batch marking of selected notifications.
*   Integration with user preferences for notification retention.
