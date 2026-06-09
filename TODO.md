# Million Memory Project Reporting — TODO

Reference list of outstanding work. Newest priorities near the top.

## Planned

1. **Automatic data sync (cron job)** — schedule `stripe:sync-transactions`,
   `woocommerce:sync-orders`, and `reports:reconcile-stripe-woocommerce` to run
   on a recurring basis (e.g. nightly) instead of only via the manual Re-Sync
   button. Wire up Laravel's scheduler (`routes/console.php` / `Schedule`) and a
   system cron entry running `php artisan schedule:run` every minute.
2. **MMP Squarespace sales integration** — pull Million Memory Project sales from
   Squarespace (Commerce / Orders API) alongside the existing WooCommerce + Stripe
   sources, so reporting covers the parent company, not just the Sound Archive
   Books imprint. Needs: a sync command, storage table(s), and reconciliation /
   roll-up into the same dashboards.
3. **Exports page** — reconciled sales, product groups, customers, state sales,
   and Stripe transaction exports (replaces the old standalone Stripe export).
   (Per-group order CSV export already exists on the Product Group Report.)
4. **Queue the Re-Sync action** — for production, make the Re-Sync button (and
   the cron syncs) run as background/queued jobs so requests don't hang.

## Production prep

- **Switch the email sender to kevin@millionmemoryproject.org** — currently sends
  as `@soundarchivebooks.org`. Update `MAIL_FROM_ADDRESS` + `MAIL_USERNAME` (and a
  new Google App Password) and `APP_NAME` in `.env`; verify deliverability.
- Set `APP_URL` to the real domain so invitation/reset links point correctly.
- Switch mail off local dev assumptions as needed (SMTP already configured).
- Confirm queue worker is running once Re-Sync/cron move to queued jobs.

## Decisions

- **API keys stay in `.env`** (Stripe + WooCommerce). Decided not to build an
  in-app admin key-management UI — fewer places for a secret to leak. Edit keys
  by hand in `.env` (and clear the config cache in production).

## Verify / QA

- **Create a Manager user and test the full workflow** — invite via the Users
  page → confirm the invitation email → set password via the link → sign in →
  run reports; confirm the Manager is blocked from Users/admin and can edit only
  their own profile.

## Done (for reference)

- Homepage `/` → Sales Dashboard; route/CSS cleanup.
- Auth: login-only, forgot/reset password, profile (name/email/password).
- Story Lab brand styling applied to the master CSS.
- Mail via Google Workspace SMTP.
- Roles (administrator/manager) + admin Users management.
- Invited users emailed + forced to set their own password.
- Password fields support Raycast snippet expansion + strength meter.
- Rebranded app to Million Memory Project Reporting.
- Richer Product Group Report — raw products, level breakdown, customers, KPIs, CSV export.
