# Plan: Translate Notifications to Bahasa Indonesia

## Phase 1: Custom Workflow Notifications

- [x] **Task 1: Write Tests for Workflow Notifications**
  - Update `tests/Feature/ApprovalServiceTest.php` to assert the new Bahasa Indonesia notification titles.
  - Target: Failing tests (Red).

- [x] **Task 2: Translate ApprovalProcessingService Notifications**
  - Update `notifyStockRequest` and `notifyStockUsage` in `app/Services/ApprovalProcessingService.php`.
  - Replace English titles and bodies with Bahasa Indonesia equivalents.
  - Target: Passing tests (Green).

- [x] **Task: Conductor - User Manual Verification 'Workflow Notifications' (Protocol in workflow.md)**

## Phase 2: ATK Resource Notifications

- [x] **Task 3: Update ATK Resource Notifications**
  - Update `AtkItemResource`, `AtkStockRequestResource`, `AtkStockUsageResource`, etc.
  - Translate `successNotificationTitle` and manual `Notification::make()` calls.

- [x] **Task 4: Write Tests for ATK Resources**
  - Add basic feature tests to verify that CRUD actions trigger the correct Bahasa notifications.

- [x] **Task: Conductor - User Manual Verification 'ATK Resources' (Protocol in workflow.md)**

## Phase 3: Marketing Media and Settings Notifications

- [x] **Task 5: Update Marketing Media Resource Notifications**

- [x] **Task 6: Update Remaining Settings and System Notifications**
  - Search for any remaining English `Notification::make()` calls in Actions and other Resources.
  - Perform final hardcoded translations.

- [x] **Task: Conductor - User Manual Verification 'Remaining Notifications' (Protocol in workflow.md)**

## Phase 4: Final Verification

- [x] **Task 7: Global Code Audit and Standards**
  - Run all tests to ensure no logic was broken during translation.
  - Run Laravel Pint for formatting.

- [~] **Task: Conductor - User Manual Verification 'Final Integration' (Protocol in workflow.md)**
