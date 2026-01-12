# Specification: Translate Notifications to Bahasa Indonesia

## 1. Overview
Update all system and resource notifications within the MJG ATK Management System to use Bahasa Indonesia instead of English. This will improve usability for the primary target users.

## 2. Functional Requirements
- **System Notifications:** Update custom workflow notifications (e.g., approval requests, submission alerts) to Bahasa Indonesia.
- **Resource Notifications:** Update standard "Created", "Saved", and "Deleted" notifications in all Filament resources to use appropriate Bahasa Indonesia equivalents.
- **Direct Implementation:** Replace existing English strings directly in the PHP classes with the translated versions.

## 3. Technical Requirements
- **Files to Update:**
    - `app/Services/ApprovalProcessingService.php` (Custom workflow notifications)
    - All Filament Resource files under `app/Filament/Resources/` (Standard CRUD notifications)
    - Any other classes using `Filament\Notifications\Notification`.
- **Translation Logic:** Direct hardcoding of strings in `->title()` and `->body()` methods.

## 4. Acceptance Criteria
- All pop-up and database notifications seen by the user are in Bahasa Indonesia.
- No remaining English notification strings in the system and resource logic.
- Notifications remain correctly categorized (Success, Warning, Danger, Info).
