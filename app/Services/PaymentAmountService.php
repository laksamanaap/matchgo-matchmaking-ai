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
        $dp = $cost->dp_per_team ?? (int) ($cost->cost_per_team ?? 0);

        return (int) ($dp + ($cost->handling_fee ?? (int) ceil($dp * 0.1)));
    }

    public function autoMatchAmount(MatchCost $cost): int
    {
        $amount = $cost->cost_per_team ?? 0;

        return (int) ($amount + ($cost->handling_fee ?? (int) ceil($amount * 0.1)));
    }
}
