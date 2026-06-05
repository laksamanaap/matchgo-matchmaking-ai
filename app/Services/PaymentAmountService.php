<?php

namespace App\Services;

use App\Models\FutsalMatch;
use App\Models\MatchCost;

class PaymentAmountService
{
    public function amountForMatch(FutsalMatch $match, ?MatchCost $cost = null): int
    {
        $cost ??= $match->matchCost;

        if (! $cost) {
            return 0;
        }

        return $match->isAutoMatch()
            ? $this->autoMatchAmount($cost)
            : $this->regularMatchAmount($cost);
    }

    public function regularMatchAmount(MatchCost $cost): int
    {
        return (int) (($cost->dp_per_team ?? (int) ceil($cost->cost_per_team * 0.5))
            + ($cost->handling_fee ?? (int) ceil($cost->total_cost * 0.1)));
    }

    public function autoMatchAmount(MatchCost $cost): int
    {
        return (int) (($cost->cost_per_team ?? 0)
            + ($cost->handling_fee ?? (int) ceil($cost->total_cost * 0.1)));
    }
}
