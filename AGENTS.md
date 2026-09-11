# AGENTS.md

Guidance for AI coding agents working on this repository. Read this before making changes.

## What this project is

A merchant-facing win-back dashboard for loyalty programs. It surfaces customers who are at the same time close to a reward and slipping away, and lets the merchant send a one-click personalized reminder. Stack: Laravel, Inertia.js, Vue 3, Bootstrap 5, PostgreSQL. Deployed on Render from the main branch.

## The core rule (most important)

The whole product is one insight: a customer is a win-back candidate only when BOTH signals hold.

- Proximity: points balance is at or above proximity_threshold of the next reward tier.
- Inactivity: last activity is at or older than inactivity_days.

Both thresholds live in config/loyalty.php. Never hardcode them. The rule is implemented in app/Services/WinBackService.php, the status is an enum in app/Enums/LoyaltyStatus.php, and the payload sent to the frontend is shaped by app/Http/Resources/CustomerWinBackResource.php. If you change the rule, update tests/Feature/WinBackServiceTest.php.

## Where things live

- Business logic: app/Services/ (WinBackService for detection, PurchaseService for the earning rule, ReminderService for the reminder write; keep it here, not in controllers).
- Dashboard page: resources/js/Pages/Dashboard.vue.
- Dashboard components: resources/js/Components/ (MetricCard, StatusBadge, WinBackList, CustomerProgressBar).
- Formatters and view state: resources/js/formatters/, resources/js/composables/useWinBackDashboard.ts.
- Types: resources/js/types/loyalty.ts.
- Reasoning behind the design: docs/DECISIONS.md.

## Commands

Setup:

    composer install
    npm install
    cp .env.example .env
    php artisan key:generate
    # set DB_CONNECTION=pgsql in .env
    php artisan migrate --seed

Run (two terminals):

    npm run dev
    php artisan serve

Checks, all must pass before a change is done:

    php artisan test     # backend
    npm test             # frontend (vitest)
    npm run typecheck    # vue-tsc, must be clean

Demo login: demo@kangaroo.test / password

## Conventions

- Do not use em dashes anywhere: code, comments, commit messages, or UI copy. Use commas, semicolons, or separate sentences.
- Keep the architecture deliberately simple. This is an MVP with one detection rule. Do not add layers, patterns, or abstractions the scope does not need.
- Thresholds come from config, never hardcoded.
- Business logic stays in the service; controllers stay thin.
- Keep the seed idempotent (the guard that returns early when data already exists).
- Every change must keep all tests green and keep the app deployable from main.

## Do not build these

- No separate REST API or API versioning. This is an Inertia monolith.
- No reward management CRUD.
- No AI or LLM calls. The detection is deterministic and explainable by design.
- No multi-tenant auth in this pass.