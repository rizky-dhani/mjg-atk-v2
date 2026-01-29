# Proposal: Pin Specific Approver to Approval Step

## Problem
Currently, an `ApprovalFlowStep` is defined by a `role_id` and an optional `division_id`. Any user who has the specified role and belongs to the specified division (or the requester's division if `division_id` is null) can approve that step. 

In some organizational scenarios, even if multiple users share the same role and division, only one specific individual should be responsible for a particular approval step. The current system lacks the precision to restrict approval to a single designated user.

## Proposed Solution
We propose adding an optional `user_id` field to the `ApprovalFlowStep` model. 
- If `user_id` is specified, only that exact user can approve the step, regardless of their role or division (though normally they would still have the role/division for consistency).
- If `user_id` is null, the system falls back to the existing role + division based matching.

This change involves:
1.  Adding a `user_id` column to the `approval_flow_steps` table.
2.  Updating the `ApprovalFlowStep` model and its Filament `RelationManager`.
3.  Updating the `ApprovalValidationService` and `ApprovalProcessingService` to respect this new constraint.

## Impact
- **Database**: New nullable foreign key `user_id` on `approval_flow_steps`.
- **UI**: New optional User selection in the Approval Flow Steps form.
- **Logic**: Precision-targeted approvals for specialized workflows.
- **Backward Compatibility**: Fully backward compatible as `user_id` is optional.
