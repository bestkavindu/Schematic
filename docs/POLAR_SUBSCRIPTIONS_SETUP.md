# Polar Subscriptions — Go-Live Checklist

How to take the Polar.sh subscription feature from "code merged" to "accepting real
payments". The integration uses [`danestves/laravel-polar`](https://github.com/danestves/laravel-polar)
(community Laravel adapter — Cashier-style API).

**Plan model:** one paid **Team** product with two prices (monthly + yearly).
**What paid unlocks:** project cap is lifted (free = 3 projects, paid = unlimited).

---

## 0. Prerequisites

- A Polar account: https://polar.sh (use **Sandbox** first: https://sandbox.polar.sh)
- The app deployed somewhere Polar can reach over HTTPS (for webhooks). Locally use a
  tunnel (ngrok / Cloudflare Tunnel / Expose / Laravel Herd share).

---

## 1. Create the product in Polar (Sandbox first)

1. Sandbox dashboard → **Products → New Product**.
2. Name it `Team`.
3. Add **two prices** on that product:
   - **Monthly** — e.g. $12 / month, recurring.
   - **Yearly** — e.g. $120 / year, recurring.
4. Save. Copy each **price/product id** (looks like `xxxxxxxx-xxxx-...`). You need both.

> The `checkout()` call passes a product id. If your monthly and yearly are two
> *prices on one product*, use the **price ids**; if they are two separate products,
> use the **product ids**. Either works — paste whatever id the checkout opens with the
> correct price into the env vars below.

---

## 2. Get the API access token

1. Sandbox dashboard → **Settings → Developers** (or *API Keys*).
2. Create an **Organization Access Token**. Copy it (shown once).

---

## 3. Configure the webhook

1. Sandbox dashboard → **Settings → Webhooks → Add endpoint**.
2. URL: `https://<your-host>/polar`  ← the package serves this route automatically.
3. Format: **Raw** (the package verifies the Standard Webhooks signature).
4. Subscribe to at least these events:
   - `order.created`, `order.updated`, `order.paid`
   - `subscription.created`, `subscription.updated`, `subscription.active`,
     `subscription.canceled`, `subscription.revoked`
   - `customer.created`, `customer.updated`, `customer.deleted`
5. Save, then copy the **webhook secret**.

> Webhooks are why the gate works: when a checkout completes (or a sub renews/cancels),
> Polar calls `/polar`, the package syncs the local `polar_subscriptions` row, and
> `User::subscribed()` flips. No webhook = the user pays but the cap never lifts.

---

## 4. Fill the environment

Edit `.env` (already scaffolded with blank keys):

```dotenv
POLAR_ACCESS_TOKEN=polar_oat_xxxxxxxxxxxx      # step 2
POLAR_WEBHOOK_SECRET=whsec_xxxxxxxxxxxx        # step 3
POLAR_SERVER=sandbox                           # 'production' when going live
POLAR_PATH=polar                               # webhook/route base; keep as-is
POLAR_TEAM_MONTHLY=<monthly product/price id>  # step 1
POLAR_TEAM_YEARLY=<yearly product/price id>    # step 1
```

Then:

```bash
php artisan config:clear
php artisan migrate          # if not already run; creates polar_* tables
```

---

## 5. Smoke test (Sandbox)

1. Register / log in as a normal user.
2. Create **3 projects** → the **4th** is blocked with the "hit the free limit" upgrade modal.
3. Landing page **Pricing** → toggle Monthly/Yearly → click **Start with Team**.
   - Guests get bounced to login, then returned to checkout.
4. Pay with a Polar **sandbox test card**.
5. After redirect back, the webhook should land within seconds. Verify:
   ```bash
   php artisan tinker
   >>> App\Models\User::find(<id>)->subscribed();   # true
   ```
6. The schema dashboard now shows **Team plan**, the cap is gone, and the 4th project
   creates successfully.
7. Account menu → **Manage billing** → opens the Polar customer portal. Cancel there →
   stays valid until period end (grace period), then `subscribed()` returns false and the
   cap re-applies.

Webhook not arriving? Check **Settings → Webhooks → Deliveries** in Polar for the response
code, and confirm `/polar` is reachable over HTTPS and excluded from CSRF (it is, in
`bootstrap/app.php`).

---

## 6. Going to Production

Repeat steps 1–4 against the **production** Polar dashboard (separate org, separate
products, new token + webhook secret), then:

```dotenv
POLAR_SERVER=production
POLAR_ACCESS_TOKEN=<prod token>
POLAR_WEBHOOK_SECRET=<prod webhook secret>
POLAR_TEAM_MONTHLY=<prod monthly id>
POLAR_TEAM_YEARLY=<prod yearly id>
```

```bash
php artisan config:cache
```

Production checklist:
- [ ] Production product + monthly/yearly prices created
- [ ] Production access token in env
- [ ] Production webhook → `https://<prod-host>/polar` + secret in env
- [ ] `POLAR_SERVER=production`
- [ ] `php artisan migrate --force` run on prod
- [ ] `config:cache` refreshed after env change
- [ ] Real-card test purchase + verified the cap lifts, then refunded/cancelled
- [ ] Update displayed prices in `resources/views/home.blade.php` if they differ from $12/$120

---

## Where things live (for maintenance)

| Concern | File |
|---|---|
| Plan ids / server / secrets | `config/polar.php`, `.env` |
| Billable user + limit logic | `app/Models/User.php` (`canCreateProject`, `projectLimit`, `FREE_PROJECT_LIMIT`) |
| Checkout + portal | `app/Http/Controllers/BillingController.php`, `routes/web.php` |
| Project-cap enforcement | `app/Livewire/Schema/Index.php` (`newProject`) |
| Dashboard usage + upgrade UI | `resources/views/livewire/schema/index.blade.php` |
| Pricing page toggle + CTA | `resources/views/home.blade.php` |
| Webhook CSRF exclusion | `bootstrap/app.php` |
| Tests | `tests/Feature/Billing/ProjectLimitTest.php` |

## Notes / caveats

- `danestves/laravel-polar` is **community-maintained** ("use at your own discretion") —
  there is no official Polar Cashier driver. It mirrors Cashier's API.
- Polar is a **merchant of record** — it handles VAT / sales tax, so no separate tax setup.
- Changing the free limit: edit `User::FREE_PROJECT_LIMIT` (also update the "3 projects"
  copy on the pricing page).
