# Engineering Decisions

This document records why the win-back dashboard is built the way it is. It
complements the README (product framing), TECHNICAL_SPEC, and BUILD_PLAN,
which all stay as they are.

## The product insight this codebase is built around

The recoverable segment is the intersection of two signals, not either one
alone:

- **close to a reward**: points balance at or above `proximity_threshold` of
  the next reward's threshold;
- **inactive**: no activity for at least `inactivity_days`.

"Close" on its own includes active customers who need no nudge. "Inactive" on
its own includes low-value customers not worth chasing. Only the overlap,
close and slipping, is worth a merchant's attention. Every part of the system
exists to compute and act on that overlap, and nothing is added that does not
serve it.

## Retention, not a promotion simulator

The benchmark submission we studied is a promotion profitability simulator:
everything is computed in memory, there is no database, and the product
answers "is this promo worth running". That is a different question from ours.

We deliberately kept the retention angle: detect the recoverable segment from
real data, rank it, and close the loop (send a reminder, simulate the return
purchase, watch the customer leave the list). That choice is what gives us a
database, real query performance to reason about, and a measurable product
loop. We adopted the benchmark's good engineering habits (serialization
boundary, domain enum, pure formatters, frontend tests) and skipped the parts
that only make sense for a stateless calculator.

## A deliberately simple architecture

There is one `WinBackService`. There are no layered domain folders, no
strategy hierarchy, no factory, and no repository interfaces. A single
detection rule does not need them, and the indirection would make the rule
harder to read, not easier.

Within that constraint we still made the logic explicit rather than implicit:

- **`App\Enums\LoyaltyStatus`** classifies every customer by the two signals
  (`WIN_BACK`, `ENGAGED_CLOSE`, `FADING_FAR`, `HEALTHY`, `NO_NEXT_REWARD`,
  `NEVER_ACTIVE`). The win-back rule is now a named case, not a bare boolean.
- **A structured `reason`** travels with each customer: the proximity signal
  (balance, threshold, percent) and the inactivity signal (days, limit), in
  plain values. It is data and text, so the dashboard can explain a
  classification without recomputing it.
- **`CustomerWinBackResource`** is the explicit contract sent to the frontend.
  Raw Eloquent columns, timestamps, and internal flags do not cross the
  boundary.
- **Thresholds come from `config('loyalty.*')`**, never hardcoded, so the
  behaviour is transparent and tunable per merchant.

## Filtering pushed into SQL

`winBackCandidates()` does not hydrate the customers table and filter in PHP.
It applies the cheap, coarse signals in the database:

- inactivity as a predicate on `last_activity_at`;
- proximity as a correlated subquery for the smallest reward threshold above
  the balance, keeping rows where the balance clears
  `proximity_threshold * next_threshold`.

Only the reduced candidate set is hydrated and decorated, and `classify()`
stays the final authority on `WIN_BACK`. A migration adds indexes on
`customers.last_activity_at` and `rewards.points_required` to support the
scan and the subquery.

This is the part of the system where the engineering is most defensible: the
segment is computed by the database at scale, not reconstructed in memory,
and the query is something we can profile and tune.

## The frontend stays on Bootstrap defaults

An earlier attempt at a bespoke dashboard redesign degraded the UI and was
abandoned. The bar for the frontend is correct, clearly organized
information, not visual polish.

- Stock Bootstrap components as they ship: card, list group, table, badge,
  button, toast. No custom CSS, no colour theme, no design system, no
  bespoke component styling.
- New win-back frontend code is typed. TypeScript was adopted incrementally
  for this code only, not as a full migration of the existing scaffold.
- Display formatting lives in a small pure `formatters/` module rather than
  inline in components, and it is unit tested.
- One light composable, `useWinBackDashboard`, derives list view state
  (sorted list, empty flag). There is no advisor engine behind it.

## Value framing

`summary()` reports revenue at risk as the total historical spend of the
win-back segment, the money that leaves if those customers do not come back.
It is never a sum of missing points labelled as value.

## What would come next

- Pluggable recommendation levers (which nudge, when, through which channel)
  once the basic loop is proven to move customers off the list.
- More segments: lapsed high value, never redeemed, first purchase never
  followed up.
- A real notification channel so a reminder actually reaches the customer
  instead of being recorded.
- A wider TypeScript migration and Vitest coverage of the remaining scaffold.
- A small API surface if a second consumer (a mobile app, a scheduled job)
  appears. It is deliberately absent today because there is only one consumer.
