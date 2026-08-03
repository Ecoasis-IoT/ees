# EES — tester feedback (fixes applied)

**Build reference:** `version.json` (see `cache_version` / `updated_at` after deploy)  
**Date:** 2026-04-16

This document summarizes the issues raised during testing and what was changed in the codebase. Please redeploy to your staging/production host and re-test.

---

### 1. Reset password link not working

**Cause:** Emails used a hard-coded URL pattern (`…/core/reset-password?token=…`). On deployments where the app is served at the site root with extensionless routing, the correct public URL is `…/reset-password?token=…` (no `/core/` segment).

**Fix:** The reset link is now built with `BASE_URL` + `ees_url_path('reset-password.php?token=…')` so it matches the same routing rules as the rest of the app.

**File:** `core/scripts/password_reset_email.php`

---

### 2. Main dashboard — “kWh total” on the same line as the value (e.g. 29.6k)

**Fix:** The “Today’s Production” KPI now shows a single line such as `29.6k kWh total` in one stat value (the separate “kWh total” line under it was removed).

**Files:** `core/dashboard.php`, `core/assets/js/pages/dashboard.js`

---

### 3. Zoom on charts

**Fix:**

- **Main dashboard** chart: added Hammer.js + chartjs-plugin-zoom; wheel / pinch zoom and Ctrl+pan where supported.
- **Site dashboard** (standard): zoom enabled on the hourly **Production** and **Weather** charts (KPI chart already had zoom).
- **Plant report** charts: same zoom plugin options on both main charts.
- **Query** page: Hammer.js added before Chart.js (required by chartjs-plugin-zoom); existing custom chart zoom config retained.

**Files:** `core/dashboard.php`, `core/assets/js/pages/dashboard.js`, `core/site-dashboard.php`, `core/assets/js/pages/site-dashboard.js`, `core/plant.php`, `core/assets/js/pages/plant.js`, `core/query.php`

---

### 4. Site dashboard — download icon not visible (Plant KPI card)

**Cause:** Theme styles made the control low-contrast; secondary button + icon did not read clearly on the card header.

**Fix:** Switched to outline buttons, added a `page-site-dashboard` body class, and CSS so download and reset controls use explicit text/border colors. Added `sr-only` helper text for accessibility.

**Files:** `core/site-dashboard.php`, `core/assets/css/pages/site-dashboard.css`

---

### 5–8. Riche Terre Mall / Bo Valon Mall — TX / PVDB / meters only partly showing

**Cause:** The main dashboard linked **every** site to `site-dashboard.php`. Only two legacy numeric IDs were special-cased in JavaScript for `site-dashboardv2` / `site-dashboardv3`. If your database uses different `tbl_site.id` values, Riche Terre and Bo Valon opened the **wrong** dashboard (single-meter UI).

**Fix:** `scripts/get_dashboard.php` now adds a `dashboard_href` per site from `ees_db_key(tbl_site.db_name)`:

- `rtm` → `site-dashboardv3` (TX 1–3, three meter streams in KPI when data exists)
- `bovalon` → `site-dashboardv2` (PVDB 1–2)
- all others → `site-dashboard`

The dashboard table uses `dashboard_href` for links (hardcoded IDs removed).

**Files:** `core/scripts/get_dashboard.php`, `core/assets/js/pages/dashboard.js`

*Note:* If a meter still shows “—”, the per-site database may not be receiving rows for that `meter_id` or the connection is misconfigured in `.env` / `db_key_helper.php`. That is a data/config issue, not the UI template.

---

### 9. Plant report — PDF on mobile/tablet should look like desktop

**Fix:** PDF capture now enforces a minimum layout width of **1280px** when measuring the report block (so narrow viewports don’t produce a compressed layout).

**File:** `core/assets/js/pages/plant.js`

---

### 10. Query — Custom tab: Meter / Weather checkboxes and “Add to Chart” not visible

**Cause:** `ees-theme.css` uses `!important` on `.card .header h2`, which overrode the inline `color:white` on the green “Custom” section headers. Outline buttons also blended into the background.

**Fix:** Added scoped overrides in `query.css` for `.header.custom-header h2` (white text) and for `#Custom .btn-outline-secondary` (readable text and borders).

**File:** `core/assets/css/query.css`

---

### 11. Archive — only Phoenix Mall in the list?

**Cause:** The archive site list previously included only sites where `tbl_archive` had **at least one row**. Sites with an empty archive table were hidden.

**Fix:** The list now includes every site whose database **has a `tbl_archive` table** (even if empty). You may still see only one site if others have no such table or no DB connection from the server.

**File:** `core/scripts/get_archive_sites.php`

---

### 12. Profile — 2FA not enabling

**Cause:** The PHP code expects a `tbl_user_2fa` table. On some databases this table was never created, so setup/verify failed silently in the UI.

**Fix:** `ees_ensure_tbl_user_2fa()` creates `tbl_user_2fa` if missing. It runs when storing a secret and when loading 2FA status on the profile page.

**File:** `core/common/two_factor_auth.php`

*Also ensure* `TWO_FACTOR_ENABLED=true` in `.env` if you rely on global flags elsewhere.

---

## Quick re-test checklist

1. Request password reset; open link from email — should load `reset-password` with a valid token (not 404).
2. Main dashboard — production KPI is one line ending with `kWh total`.
3. Zoom: wheel / pinch / Ctrl+pan on dashboard bar chart, site dashboard bar/line, plant charts, query custom chart.
4. Riche Terre / Bo Valon from **All Sites** table — correct multi-meter dashboard page.
5. Plant PDF from a phone/tablet — layout width similar to desktop.
6. Query → **Custom** tab — green section titles and “Add to Chart” visible.
7. Archive — multiple sites if their DBs have `tbl_archive`.
8. Profile — Set up 2FA → scan → verify → status **ENABLED**.

---

*End of document.*
