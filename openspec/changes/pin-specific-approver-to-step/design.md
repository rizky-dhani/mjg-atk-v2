# Design: Pin Specific Approver to Approval Step

## Architectural Reasoning

### Model Changes
The `ApprovalFlowStep` model will be updated to include a `user_id` foreign key. This allows a 1:1 relationship between a step and a specific user. While the model already had a `users()` many-to-many relationship placeholder, the database implementation for it was missing and the requirement specifically asks for "specific user" (singular) selection.

### Service Layer Logic
The core logic for finding and validating approvers resides in:
- `ApprovalValidationService`: Checks if a given user can approve a given model at its current step.
- `ApprovalProcessingService`: Identifies "Next Approvers" to notify them.

Both will be modified to prioritize `user_id` if it is present. If `user_id` is set, the logic will bypass role/division checks and strictly match the user's ID.

### UI Implementation
In the `ApprovalFlowStepsRelationManager`, we will add a `Select` component for `user_id`. 
To enhance UX, this selector should ideally be filtered based on the currently selected `role_id` and `division_id`. If both are selected, only users matching those criteria should be shown. If none are selected, all users are shown.

## Technical Details

### Database Migration
```php
Schema::table('approval_flow_steps', function (Blueprint $table) {
    $table->foreignId('user_id')->nullable()->after('division_id')->constrained('users')->onDelete('set null');
});
```

### Model Update
```php
public function user()
{
    return $this->belongsTo(User::class);
}
```

### Validation Logic (Pseudocode)
```php
if ($step->user_id) {
    return $user->id === $step->user_id;
}
// fallback to role + division logic
```
