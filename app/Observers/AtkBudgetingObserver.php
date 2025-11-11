<?php

namespace App\Observers;

use App\Models\AtkBudgeting;

class AtkBudgetingObserver
{
    private static $updatingRemainingAmount = [];

    /**
     * Handle the AtkBudgeting "created" event.
     */
    public function created(AtkBudgeting $atkBudgeting): void
    {
        $key = $atkBudgeting->getKey();
        
        // Ensure the remaining amount is calculated when the budget is created
        if (! isset(self::$updatingRemainingAmount[$key])) {
            $atkBudgeting->remaining_amount = $atkBudgeting->calculateRemainingAmount();
            self::$updatingRemainingAmount[$key] = true;
            $atkBudgeting->save();
            unset(self::$updatingRemainingAmount[$key]);
        }
    }

    /**
     * Handle the AtkBudgeting "updated" event.
     */
    public function updated(AtkBudgeting $atkBudgeting): void
    {
        $key = $atkBudgeting->getKey();
        
        // If the budget_amount or used_amount changed, update the remaining amount
        if ($atkBudgeting->isDirty(['budget_amount', 'used_amount']) && ! isset(self::$updatingRemainingAmount[$key])) {
            $atkBudgeting->remaining_amount = $atkBudgeting->calculateRemainingAmount();
            self::$updatingRemainingAmount[$key] = true;
            $atkBudgeting->save();
            unset(self::$updatingRemainingAmount[$key]);
        }
    }

    /**
     * Handle the AtkBudgeting "deleted" event.
     */
    public function deleted(AtkBudgeting $atkBudgeting): void
    {
        //
    }

    /**
     * Handle the AtkBudgeting "restored" event.
     */
    public function restored(AtkBudgeting $atkBudgeting): void
    {
        //
    }

    /**
     * Handle the AtkBudgeting "force deleted" event.
     */
    public function forceDeleted(AtkBudgeting $atkBudgeting): void
    {
        //
    }
}
