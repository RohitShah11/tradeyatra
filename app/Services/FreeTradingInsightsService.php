<?php

namespace App\Services;

use App\Models\DailyPlan;
use App\Models\Trade;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class FreeTradingInsightsService
{
    public function answer(User $user, string $question): array
    {
        $query = Trade::query()->where('user_id', $user->id);
        $lower = Str::lower($question);
        $period = 'all recorded';

        if (Str::contains($lower, ['this month', 'monthly', 'month'])) {
            $query->whereBetween('date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()]);
            $period = now()->format('F Y');
        } elseif (Str::contains($lower, ['this week', 'weekly', 'week'])) {
            $query->whereBetween('date', [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()]);
            $period = 'this week';
        } elseif (Str::contains($lower, ['last 20', 'recent'])) {
            $query->latest('date')->latest('time')->limit(20);
            $period = 'the latest 20';
        }

        $trades = $query->get();
        $context = 'Analysing '.$trades->count().' '.Str::plural('trade', $trades->count());
        $links = [['label' => 'Open all trades', 'url' => route('trades.index')]];

        if (Str::contains($lower, ['shark', 'delta', 'compare', 'broker', 'exchange'])) {
            $answer = $this->brokerComparison($trades, $period);
            $links[] = ['label' => 'View reports', 'url' => route('trades.analysis')];
        } elseif (Str::contains($lower, ['strategy', 'setup', 'best'])) {
            $answer = $this->strategyAnalysis($trades, $period);
            $links[] = ['label' => 'Review reports', 'url' => route('trades.analysis')];
        } elseif (Str::contains($lower, ['mistake', 'losing', 'loss', 'discipline', 'emotion'])) {
            $answer = $this->lossAnalysis($trades, $period);
            $links[] = ['label' => 'Review calendar', 'url' => route('trades.calendar')];
        } elseif (Str::contains($lower, ['plan', 'checklist', 'today'])) {
            $answer = $this->planAnalysis($user, $trades);
            $links[] = ['label' => 'Open dashboard', 'url' => route('dashboard')];
        } else {
            $answer = $this->summary($trades, $period);
            $links[] = ['label' => 'Open calendar', 'url' => route('trades.calendar')];
        }

        return [
            'message' => $answer."\n\nThis is journal analysis for education and review, not financial advice.",
            'context' => $context,
            'links' => array_values(array_unique($links, SORT_REGULAR)),
        ];
    }

    private function summary(Collection $trades, string $period): string
    {
        if ($trades->isEmpty()) return "I could not find trades for {$period}. Add or sync trades and ask me again.";

        $wins = $trades->filter(fn (Trade $trade) => $trade->net_pnl > 0)->count();
        $net = $trades->sum(fn (Trade $trade) => $trade->net_pnl);
        $fees = (float) $trades->sum('trading_fees');
        $currency = $this->currency($trades);
        $winRate = round(($wins / $trades->count()) * 100, 1);

        return "For {$period}, you recorded {$trades->count()} ".Str::plural('trade', $trades->count())." with a {$winRate}% win rate. Net result: {$currency} ".number_format($net, 2)." after {$currency} ".number_format($fees, 2)." in recorded fees. ".$this->disciplineLine($trades);
    }

    private function brokerComparison(Collection $trades, string $period): string
    {
        if ($trades->isEmpty()) return "There are no trades available to compare for {$period}.";

        $rows = $trades->groupBy(fn (Trade $trade) => $trade->broker ?: 'Manual')->map(function (Collection $items, string $broker) {
            $wins = $items->filter(fn (Trade $trade) => $trade->net_pnl > 0)->count();
            return [
                'broker' => $broker,
                'count' => $items->count(),
                'net' => $items->sum(fn (Trade $trade) => $trade->net_pnl),
                'win_rate' => $items->count() ? round($wins / $items->count() * 100, 1) : 0,
            ];
        })->sortByDesc('net')->values();

        return "Exchange comparison for {$period}:\n".$rows->map(fn ($row) => "• {$row['broker']}: {$row['count']} trades, {$row['win_rate']}% win rate, net ".number_format($row['net'], 2))->implode("\n")."\nThe first row has the strongest recorded net result for this period; compare sample sizes before drawing conclusions.";
    }

    private function strategyAnalysis(Collection $trades, string $period): string
    {
        $strategies = $trades->filter(fn (Trade $trade) => filled($trade->strategy))->groupBy('strategy')->map(function (Collection $items, string $strategy) {
            return ['strategy' => $strategy, 'count' => $items->count(), 'net' => $items->sum(fn (Trade $trade) => $trade->net_pnl)];
        })->filter(fn ($row) => $row['count'] >= 2)->sortByDesc('net')->values();

        if ($strategies->isEmpty()) return "I need at least two trades tagged with the same strategy to compare setups for {$period}. Add strategy labels when editing trades.";

        $best = $strategies->first();
        $worst = $strategies->last();
        return "Your strongest tagged strategy for {$period} is {$best['strategy']} with {$best['count']} trades and net ".number_format($best['net'], 2).". The weakest in this sample is {$worst['strategy']} with net ".number_format($worst['net'], 2).". Review execution quality and sample size before increasing risk.";
    }

    private function lossAnalysis(Collection $trades, string $period): string
    {
        $losses = $trades->filter(fn (Trade $trade) => $trade->net_pnl < 0);
        if ($losses->isEmpty()) return "I found no losing trades in {$period}. Continue recording notes and discipline fields so future reviews remain useful.";

        $mistakes = $losses->flatMap(fn (Trade $trade) => $trade->mistake_tags ?: [])->filter()->countBy()->sortDesc()->take(3);
        $emotions = $losses->pluck('emotion')->filter()->countBy()->sortDesc()->take(2);
        $notFollowed = $losses->where('plan_followed', false)->count();
        $details = [];
        if ($mistakes->isNotEmpty()) $details[] = 'Frequent mistake tags: '.$mistakes->map(fn ($count, $tag) => "{$tag} ({$count})")->implode(', ').'.';
        if ($emotions->isNotEmpty()) $details[] = 'Common recorded emotions: '.$emotions->map(fn ($count, $emotion) => "{$emotion} ({$count})")->implode(', ').'.';
        $details[] = "{$notFollowed} of {$losses->count()} losing trades were marked as not following the plan.";

        return "I reviewed {$losses->count()} losing trades from {$period}. ".implode(' ', $details)." Focus the next review on the most repeated behaviour rather than changing strategy after one result.";
    }

    private function planAnalysis(User $user, Collection $trades): string
    {
        $plan = DailyPlan::query()->where('user_id', $user->id)->whereDate('plan_date', Carbon::today())->first();
        $discipline = $this->disciplineLine($trades);
        if (! $plan || blank($plan->content)) return "You have not written today's trading plan yet. Start with maximum risk, allowed setups, no-trade conditions, and a stop rule. {$discipline}";

        return "Today's recorded plan is:\n“{$plan->content}”\n{$discipline} Before the next trade, confirm the setup, risk limit, invalidation point, and stop condition.";
    }

    private function disciplineLine(Collection $trades): string
    {
        $rated = $trades->filter(fn (Trade $trade) => $trade->plan_followed !== null);
        if ($rated->isEmpty()) return 'No plan-followed data is available yet.';
        $followed = $rated->where('plan_followed', true)->count();
        return round($followed / $rated->count() * 100, 1).'% of rated trades were marked as following the plan.';
    }

    private function currency(Collection $trades): string
    {
        $currencies = $trades->pluck('currency')->filter()->unique();
        return $currencies->count() === 1 ? $currencies->first() : 'mixed currency';
    }
}
