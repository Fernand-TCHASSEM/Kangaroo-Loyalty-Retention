# Loyalty Retention: Win-Back Dashboard for Loyalty Programs

> A focused tool that helps merchants win back customers who are close to a reward but slipping away.

This is a product challenge submission for Kangaroo Rewards. It is intentionally scoped as a credible MVP rather than a complete system.

---

## The problem

Loyalty programs generate a specific, valuable, and often-ignored segment: customers who have accumulated points and are **close to unlocking a reward**, but who **stop coming back before they get there**.

These customers are among the most winnable a merchant has. They are already engaged, they have real points at stake, and they are one or two visits away from a reward. Yet most merchants have no way to see them, so these customers quietly churn while sitting on unredeemed value.

## Who experiences the problem

- **Primary user: the merchant** (or their marketing/operations staff), who wants to retain high-intent customers and increase repeat visits without blasting everyone with generic promotions.
- **Indirect beneficiary: the customer**, who receives a timely, relevant nudge instead of noise.

## The solution

A merchant-facing dashboard that:

1. Shows all customers with their points balance and progress toward their next reward.
2. **Automatically surfaces a "Win-back" segment**: customers who are *close to a reward* (at or above a configurable percentage of the threshold) **and** *inactive* (no purchase for a configurable number of days).
3. Lets the merchant **trigger a personalized win-back reminder** in one click ("You're only X points away from [reward], come back and claim it!").
4. Includes a **"Simulate purchase"** action so the merchant (and the demo) can see a customer earn points and move out of the win-back segment in real time.

### Value proposition

Turn customers who are about to churn into customers who come back, by using the *almost-earned reward* as the hook. The segment is small, high-intent, and directly tied to repeat revenue, far more actionable than a generic "inactive customers" list.

---

## Key product decisions

- **The core insight is the combination of two signals**, not either one alone. "Close to a reward" alone includes active customers who need no nudge. "Inactive" alone includes low-value customers not worth chasing. The intersection, *close AND slipping*, is the winnable segment. This is the heart of the product.
- **Win-back candidates are ranked by proximity to the reward** (fewest points needed first), because those are the easiest and cheapest to convert.
- **Reminders are personalized and specific** (name, exact points remaining, named reward), because specificity drives action.
- **Thresholds are configurable** (proximity %, inactivity days) rather than hard-coded, so the tool adapts to different merchant patterns and so the logic is transparent and defensible.

## Key technical decisions

- **Stack: Laravel + Inertia.js + Vue 3 + Bootstrap 5 + PostgreSQL.** This is close to Kangaroo's stack (Laravel, Vue, Inertia); PostgreSQL is used instead of MySQL to enable free deployment on Render, and Eloquent keeps the code database-agnostic and lets the server own the business logic while Vue owns the interactivity.
- **Inertia instead of a separate REST API.** Controllers return Vue pages with props directly, removing the need to build and maintain a separate JSON API for an MVP. Interactivity stays in Vue.
- **Business logic lives in a dedicated service** (`WinBackService`), not in controllers. Controllers stay thin: receive, delegate, return. This keeps the segmentation logic isolated, testable, and easy to explain.
- **Purchases are simulated** via a route, because building a real POS integration (Lightspeed, Shopify, WooCommerce) is out of scope for an MVP and not needed to demonstrate the idea.

## What was intentionally left out

- **Real POS integrations.** In production, transactions would arrive via webhooks from Kangaroo's POS partners. Here they are simulated. This is the single most important "not built on purpose" decision.
- **Real message delivery.** Reminders are generated and stored, not emailed or texted. Delivery would plug into an email/SMS provider later.
- **Multi-merchant auth and tenancy.** The MVP assumes a single merchant context. Multi-tenant scoping is noted as the next step.
- **Reward configuration UI.** Rewards are seeded with a couple of simple tiers rather than managed through a screen.

## What I would improve with more time

- Multi-tenant support so each merchant sees only their own customers.
- Real POS webhook ingestion and real reminder delivery (email/SMS) with delivery tracking.
- Reminder effectiveness tracking: did the customer come back after the nudge? This closes the loop and makes the value measurable.
- A/B testing of reminder copy and configurable reward tiers via the UI.

---

## Running the project

Project folder / repository name: `loyalty-retention`.

See `BUILD_PLAN.md` for setup and build steps, and `TECHNICAL_SPEC.md` for the full specification.

### Local (PostgreSQL)

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
# set the DB connection to pgsql in .env, then:
php artisan migrate --seed
npm run dev        # in one terminal
php artisan serve  # in another
```

`.env` database block:

```
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=loyalty_retention
DB_USERNAME=postgres
DB_PASSWORD=your_password
```

Requires the `pdo_pgsql` PHP extension.

### Deployment (Render, free tier)

1. Push the repo to GitHub.
2. On Render, create a free **PostgreSQL** instance; copy its Internal Database URL.
3. Create a **Web Service** from the repo (PHP runtime, or the Docker path from Render's Laravel guide).
4. Build command runs Composer install, `npm install && npm run build`, and `php artisan migrate --force --seed`.
5. Set the production env vars (APP_KEY, APP_ENV=production, APP_URL, and the DB_* values from the Render database).
6. Start command binds Laravel to the port Render assigns.

Note: the free PostgreSQL instance is fine for a demo; Render deletes free databases after 90 days, which does not affect a short evaluation window.
