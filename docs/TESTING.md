# Testing

Two suites, one principle. The backend suite proves the win-back rule, its
boundaries, and the loop that closes it. The frontend suite proves the
behaviour of the pure units and the components that carry a signal or an
action. For why the logic is shaped this way see `docs/DECISIONS.md`, for the
request flow see `docs/ARCHITECTURE.md`, for the threshold arithmetic see
`docs/DATA_MODEL.md`.

## 1. What the backend suite verifies and why

`tests/Feature`, PHPUnit, `RefreshDatabase` on an in-memory SQLite database.
No external services, no seeded data: each test builds exactly the rows it
needs.

### The two-signal core

`WinBackServiceTest` drives `WinBackService` directly. It asserts the
positive case (close to a reward and inactive is a candidate), then isolates
each signal by removing the other: close but recently active is not a
candidate, inactive but far from any reward is not a candidate. The product
rule is verified as an AND of the two signals, not either one alone.

### Boundary cases

Three tests pin the edges: a customer exactly at both thresholds is a
candidate, a customer one point below the proximity threshold is not, a
customer one day short of the inactivity window is not. These tests derive
the balance and the date from `config('loyalty.proximity_threshold')` and
`config('loyalty.inactivity_days')`, so they follow a threshold change
instead of hardcoding 80 and 14.

### Special statuses

`allCustomersWithProgress()` is used to check the two statuses that sit
outside the win-back logic: a balance past every reward threshold resolves to
`NO_NEXT_REWARD` and is never a candidate even while inactive, and a customer
with no transactions and a null `last_activity_at` resolves to
`NEVER_ACTIVE`.

### Value framing

`test_summary_reports_revenue_at_risk_as_win_back_segment_spend` builds one
candidate with two transactions and one close but active non-candidate with a
large transaction. It asserts `revenue_at_risk` is the candidate's spend
only, and that no points-sum key is present. Revenue at risk is money that
leaves, never a count of missing points.

### The HTTP surface

- `SimulatePurchaseTest`: a valid amount updates `points_balance` and
  `last_activity_at` and writes one `transactions` row at
  `points_per_dollar`; an amount of 0 is rejected by the form request and
  nothing is written.
- `ReminderTest`: reminding a candidate stores a `reminders` row whose
  message is asserted in full (name, exact points remaining, reward name);
  reminding a non-candidate returns an error and writes nothing. This is the
  stale-row guard, a merchant cannot nudge someone who is not on the list.
- `DashboardPayloadTest`: an Inertia assertion that each customer row carries
  exactly the ten declared keys, that `last_activity_at`, the timestamps,
  and internal flags are absent, and that `days_inactive` is null for a
  never-active row. This locks the serialisation contract.

### The loop closes

`WinBackLoopTest` is a single test and the most important one. A customer is
asserted on the win-back list, a simulated purchase is posted through the
real HTTP route, and the customer is asserted gone from the list afterward.
The balance still clears the proximity line, so the removal comes from
regained activity: an action moves a customer out of the segment, proven end
to end.

## 2. What the frontend suite verifies

Vitest with `@vue/test-utils` in jsdom, `resources/js/**/*.spec.ts`. A
`makeWinBackCustomer` factory in `resources/js/test-support/factories.ts`
shapes fixtures.

- `useWinBackDashboard`: the candidate list is always ordered by
  `points_needed` ascending (closest first), and the empty flag and count
  are correct. There is no ranking logic beyond the sort, so that is the
  whole surface.
- `formatters`: percent renders a 0 to 1 fraction with two decimals and a
  trailing sign, days inactive renders null as "Never active" and picks
  singular or plural, currency goes through `Intl` USD formatting and treats
  null as zero, points render as a whole number. Every number shown to the
  merchant is formatted in one pure, tested place.
- `StatusBadge`: parametrised over all six `LoyaltyStatus` cases, each mapped
  to its Bootstrap contextual class and its label, so no status can render
  blank.
- `MetricCard`: renders the label and the value, and shows the hint element
  only when a hint prop is passed.
- `WinBackList`: for a candidate, the rendered text carries both signals and
  the full plain-language reason sentence built from the `reason` payload, so
  the dashboard explains a classification without recomputing it; the empty
  state renders its message; clicking the reminder button emits the customer
  to the parent exactly once.

Together these guarantee that the list is ordered and its empty state works,
that both signals and the reason reach the screen, that every status and
number has a defined rendering, and that the one interactive action emits the
right payload.

## 3. The testing stance

This is a deliberate choice, not a coverage gap. A test earns its place by
pinning something that could plausibly regress and change product behaviour:
a threshold comparison, a null branch, a payload key, a line of user-facing
copy, the loop that closes. Framework glue, Bootstrap markup, and the Breeze
auth scaffold that ships with the project are left alone.

`Dashboard.vue` is not mounted whole. It is pinned at its two seams instead:
the props contract on the server (`DashboardPayloadTest`) and each child
component on the client. The result is a small suite that runs in seconds
where every failure names a real rule. `docs/DECISIONS.md` applies the same
principle to the architecture.

## 4. Running the suites

| Command | Scope |
| --- | --- |
| `php artisan test --testsuite=Feature` | backend feature tests, in-memory SQLite |
| `npm test` | frontend behaviour (`vitest run`) |
| `npm run typecheck` | frontend types (`vue-tsc --noEmit`) |

`composer test` clears the config cache and then runs `php artisan test`.
Local setup is in the README.
