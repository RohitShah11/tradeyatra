<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class ResourceController extends Controller
{
    private const ROUTES = [
        'delta-exchange-trading-journal' => 'resources.delta',
        'shark-exchange-trading-journal' => 'resources.shark',
        'crypto-trading-journal-india' => 'resources.crypto-india',
    ];

    public function index(): View
    {
        return view('resources.index', ['guides' => collect($this->guides())]);
    }

    public function show(string $slug): View
    {
        abort_unless(array_key_exists($slug, $this->guides()), 404);

        return view('resources.show', [
            'guide' => $this->guides()[$slug],
            'relatedGuides' => collect($this->guides())->except($slug)->take(2),
        ]);
    }

    public static function routeFor(string $slug): string
    {
        return route(self::ROUTES[$slug]);
    }

    private function guides(): array
    {
        return [
            'delta-exchange-trading-journal' => [
                'slug' => 'delta-exchange-trading-journal',
                'title' => 'Delta Exchange Trading Journal',
                'metaTitle' => 'Delta Exchange Trading Journal for India | TradeYatra',
                'description' => 'Learn how to maintain a Delta Exchange trading journal, import realized activity, review fees and P&L, and build a repeatable weekly review process.',
                'eyebrow' => 'Delta Exchange workflow',
                'intro' => 'A useful Delta Exchange journal should do more than store a list of fills. It should help you connect realized results with the decisions, market conditions, fees, and habits behind them.',
                'sections' => [
                    ['title' => 'What to record from Delta Exchange', 'paragraphs' => [
                        'Begin with the facts needed to reconstruct each completed trade: contract, direction, entry and exit context, realized profit or loss, fees, execution time, and the account used. Keep deposits, withdrawals, and trading results clearly separated so cash movement is not mistaken for performance.',
                        'TradeYatra uses a read-only connection workflow to bring supported Delta activity into a private journal. The imported record is the starting point; your notes, screenshots, setup label, and review are what turn it into useful feedback.',
                    ], 'points' => ['Realized P&L and trading fees', 'Contract and trade direction', 'Entry and exit timing', 'Setup, mistake, and market-context notes']],
                    ['title' => 'Use a safe connection process', 'paragraphs' => [
                        'Create a dedicated API key for journaling and grant only the permissions required to read account and trade history. Trading and withdrawal permissions should remain disabled. A separate key is easier to audit and revoke without affecting other tools.',
                        'After saving the connection, test it before the first import. If the exchange supports IP restrictions, apply the server addresses shown in TradeYatra’s connection guide.',
                    ]],
                    ['title' => 'Review the journal every week', 'paragraphs' => [
                        'A weekly review is more useful than checking P&L after every trade. Group results by setup, market, day, and direction. Then compare your profitable trades with your losing trades and look for behaviour you can control.',
                        'Choose one improvement for the next week—for example, avoiding late entries or recording a screenshot before moving a stop. A short, specific rule is easier to follow than a long list of intentions.',
                    ], 'points' => ['Check net results after fees', 'Separate strategy losses from execution mistakes', 'Review screenshots of the largest wins and losses', 'Write one rule for the next trading week']],
                ],
                'faq' => [
                    ['q' => 'Does TradeYatra place trades on Delta Exchange?', 'a' => 'No. TradeYatra is a journal and review tool. Its connection workflow is designed for reading supported account history, not placing or managing orders.'],
                    ['q' => 'Should deposits be counted as trading profit?', 'a' => 'No. Deposits and withdrawals are cash movements and should be separated from realized trading performance.'],
                ],
            ],
            'shark-exchange-trading-journal' => [
                'slug' => 'shark-exchange-trading-journal',
                'title' => 'Shark Exchange Trading Journal',
                'metaTitle' => 'Shark Exchange Trading Journal & Review Guide | TradeYatra',
                'description' => 'Build a Shark Exchange trading journal with imported history, screenshots, notes, P&L review, and a practical process for improving trading discipline.',
                'eyebrow' => 'Shark Exchange workflow',
                'intro' => 'A Shark Exchange trading journal creates a permanent review process around your trade history. The goal is not to watch numbers constantly; it is to understand which decisions are repeatable and which mistakes are costing you.',
                'sections' => [
                    ['title' => 'Turn trade history into a decision record', 'paragraphs' => [
                        'Exchange history tells you what was executed, but it rarely explains why. For every important trade, add the setup you believed you were trading, the reason for entry, the invalidation point, and whether you followed the plan.',
                        'Screenshots are especially valuable when a numerical result hides a poor decision. A winning trade can still involve excessive risk, while a losing trade can be a correctly executed setup.',
                    ], 'points' => ['Setup and entry reason', 'Planned risk and invalidation', 'Before-and-after chart screenshots', 'Execution grade independent of P&L']],
                    ['title' => 'Connect with minimum permissions', 'paragraphs' => [
                        'Use a dedicated Shark Exchange API key and keep order placement and withdrawals disabled. Save credentials only in the secure connection settings—not in notes, screenshots, email, or support messages.',
                        'Enable automatic synchronization only when you want TradeYatra to refresh supported history on the platform schedule. You can still use manual sync when you prefer to control when imports happen.',
                    ]],
                    ['title' => 'Create a monthly feedback loop', 'paragraphs' => [
                        'At month-end, compare performance by setup, weekday, direction, and market. Look beyond the total return: consistency, fees, avoidable mistakes, and the size of losing days often reveal more than a single headline number.',
                        'Keep the review actionable. Preserve the behaviours that worked, remove one recurring mistake, and define the conditions under which you should not trade.',
                    ], 'points' => ['Compare gross and net performance', 'Find the setups with enough samples to evaluate', 'Identify the costliest repeated mistake', 'Set one measurable process goal']],
                ],
                'faq' => [
                    ['q' => 'Can I journal Shark Exchange trades manually?', 'a' => 'Yes. Manual journal records remain useful when you do not want to connect an exchange or when you need to add context beyond imported history.'],
                    ['q' => 'Why add screenshots if trades are imported?', 'a' => 'Imported records preserve execution facts; screenshots preserve chart context and make decision quality easier to review later.'],
                ],
            ],
            'crypto-trading-journal-india' => [
                'slug' => 'crypto-trading-journal-india',
                'title' => 'Crypto Trading Journal for India',
                'metaTitle' => 'Crypto Trading Journal for Indian Traders | TradeYatra',
                'description' => 'A practical crypto trading journal process for Indian traders: record trades, separate cash flows, review P&L, account for fees, and improve discipline.',
                'eyebrow' => 'Journal fundamentals',
                'intro' => 'A crypto trading journal gives Indian traders one place to organize trades, screenshots, notes, and review data across supported exchanges. It is a decision-review tool—not a promise of profit or a replacement for exchange records.',
                'sections' => [
                    ['title' => 'What a useful crypto journal contains', 'paragraphs' => [
                        'Record enough information to understand the trade without reopening the exchange: market, direction, entry and exit, realized result, fees, setup, planned risk, and a short explanation of the decision.',
                        'Use consistent labels. If the same setup has several different names, your reports will fragment the sample and make comparisons less reliable.',
                    ], 'points' => ['Execution facts and net P&L', 'Setup and market condition', 'Risk plan and rule compliance', 'Notes and chart evidence']],
                    ['title' => 'Keep performance and cash flow separate', 'paragraphs' => [
                        'Deposits increase account balance but are not trading profit. Withdrawals reduce balance but are not trading losses. Keeping these movements separate prevents distorted performance reports.',
                        'Fees also matter. A strategy can appear positive before costs and negative after frequent execution fees. Review net outcomes and the amount of activity required to produce them.',
                    ]],
                    ['title' => 'Use daily, weekly, and monthly reviews', 'paragraphs' => [
                        'A daily note should be brief: what you planned, what you traded, and whether you followed your rules. Weekly reviews compare setups and mistakes. Monthly reviews examine broader patterns and whether the process is becoming more consistent.',
                        'Do not change a strategy after one result. Use an adequate sample, record changes clearly, and avoid mixing several experimental rules at once.',
                    ], 'points' => ['Daily: record plan and compliance', 'Weekly: compare setups and mistakes', 'Monthly: evaluate patterns and costs', 'Next period: choose one process improvement']],
                ],
                'faq' => [
                    ['q' => 'Is a trading journal financial advice?', 'a' => 'No. A journal organizes your own records and observations. It does not recommend trades or guarantee improved results.'],
                    ['q' => 'Can one journal include multiple exchanges?', 'a' => 'Yes. TradeYatra keeps supported Shark and Delta activity in one private review workspace while preserving the broker attached to each record.'],
                ],
            ],
        ];
    }
}
