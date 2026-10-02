# Deploying LostMate AI to Railway

Railway was chosen over Render because it has a native MySQL plugin (this
project's database engine is fixed as MySQL - see CLAUDE.md) and properly
supports a long-running queue worker process, which Render's free tier
doesn't.

The app ships with a `Dockerfile` so Railway builds and runs it the same
way anywhere - no platform-specific magic. Three services run from the
same image, each with a different start command:

| Service     | Start command                                    | Purpose                                   |
|-------------|---------------------------------------------------|--------------------------------------------|
| `web`       | (the Dockerfile's default `CMD`)                   | Serves the site over HTTP                  |
| `worker`    | `php artisan queue:work --tries=3 --max-time=3600` | Processes AI matching, notifications       |
| `scheduler` | `php artisan schedule:work`                        | Runs the daily auto-close-returned-items job |

## 0. Push this repo to GitHub

Railway deploys from a GitHub repository, so this project needs to live
there first (it's currently only a local git repo).

1. Create a new, empty repository at [github.com/new](https://github.com/new)
   (don't initialize it with a README - this project already has one).
2. From the project folder:
   ```
   git remote add origin https://github.com/<your-username>/<repo-name>.git
   git push -u origin master
   ```

## 1. Create the Railway project

1. Go to [railway.app](https://railway.app) and sign up (GitHub login is fastest).
2. **New Project → Deploy from GitHub repo** → pick the repository you just pushed.
   Railway will detect the `Dockerfile` and start a build automatically -
   let that first build fail for now, there's no database yet.

## 2. Add MySQL

1. In the project, **New → Database → Add MySQL**.
2. Railway provisions it and exposes connection variables automatically
   (`MYSQLHOST`, `MYSQLPORT`, `MYSQLDATABASE`, `MYSQLUSER`, `MYSQLPASSWORD`).

## 3. Configure the `web` service's environment variables

Open the `web` service → **Variables** and add:

```
APP_NAME=LostMate AI
APP_ENV=production
APP_DEBUG=false
APP_KEY=                         # leave blank for now, see step 4
APP_URL=                         # leave blank for now, see step 5

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=${{MySQL.MYSQLHOST}}
DB_PORT=${{MySQL.MYSQLPORT}}
DB_DATABASE=${{MySQL.MYSQLDATABASE}}
DB_USERNAME=${{MySQL.MYSQLUSER}}
DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
FILESYSTEM_DISK=local

# Email - needed for verification links and match/claim alerts.
# Example uses a Gmail "App password" (Google Account > Security > App passwords).
# Any SMTP provider works (Mailtrap, Brevo, your school's mail server).
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_SCHEME=null
MAIL_USERNAME=your.address@gmail.com
MAIL_PASSWORD=your-16-character-app-password
MAIL_FROM_ADDRESS=your.address@gmail.com
MAIL_FROM_NAME="LostMate AI"

# Only school emails can register (comma-separated). Leave empty to allow any.
SCHOOL_EMAIL_DOMAINS=school.edu.ph

# New users must click an emailed link before using the app. If the MAIL_*
# settings above aren't working yet, set this to false - otherwise new users
# never receive their link and are stuck on "Check your inbox".
REQUIRE_EMAIL_VERIFICATION=true

# AI matching - pick ONE of these two blocks:

# Option A: fake mode (free, deterministic, fine for a demo)
AI_MATCHING_FAKE=true
OPENAI_API_KEY=
OPENAI_MODEL=gpt-4o-mini

# Option B: real OpenAI (costs a few cents per test run)
# AI_MATCHING_FAKE=false
# OPENAI_API_KEY=sk-...
# OPENAI_MODEL=gpt-4o-mini
# AI_MATCHING_PHOTOS=true   # optional: AI also compares item photos (a bit more per run)

# Lost & found office, and how long before unclaimed items can be donated/disposed.
LOSTMATE_OFFICE_NAME="Guidance Office"
LOSTMATE_OFFICE_HOURS="Mon-Fri, 8:00 AM - 5:00 PM"
LOSTMATE_UNCLAIMED_AFTER_DAYS=60

VITE_APP_NAME=LostMate AI
```

The `${{MySQL.MYSQLHOST}}` syntax is Railway's variable reference - it
auto-fills from the MySQL service you just added, so you never type the
actual password anywhere.

## 4. Generate a production APP_KEY

Don't reuse your local `.env`'s key. In the `web` service, open the
**Deployments** tab once it's built, click the three-dot menu → **Shell** (or
use the Railway CLI: `railway run php artisan key:generate --show`) and
run:

```
php artisan key:generate --show
```

Copy the `base64:...` value it prints into the `APP_KEY` variable.

## 5. Set APP_URL to Railway's domain

1. In the `web` service → **Settings → Networking → Generate Domain**.
   Railway gives you something like `lostmate-ai-production.up.railway.app`.
2. Copy that into `APP_URL` as `https://lostmate-ai-production.up.railway.app`
   (with `https://`, no trailing slash).
3. Redeploy (Railway does this automatically when you save a variable).

## 6. Add a persistent volume for uploaded photos

Container filesystems are wiped on every redeploy. Everything in this app
that matters is already in the database (sessions, cache, queue jobs) -
*except* uploaded photos (item photos, avatars, claim proof images), which
live on disk at `storage/app/public`.

1. `web` service → **Settings → Volumes → New Volume**.
2. Mount path: `/var/www/html/storage/app/public`
3. Redeploy.

Without this step, every photo uploaded between deploys disappears the
next time you push a change.

## 7. Add the `worker` service

1. **New → GitHub Repo** → same repository again (Railway lets you add the
   same repo as a second service in one project).
2. That service's **Settings → Deploy → Custom Start Command**:
   ```
   php artisan queue:work --tries=3 --max-time=3600
   ```
3. **Variables** → click **Add Reference** (or copy the same list from
   `web`) - the worker needs the same `DB_*`, `APP_KEY`, `OPENAI_*`,
   `AI_MATCHING_FAKE`, and `MAIL_*` variables as `web` to do its job
   (the worker is what actually sends emails). It does **not**
   need the storage volume (it never touches uploaded files).

## 8. Add the `scheduler` service

Same as step 7, but:
- Custom Start Command: `php artisan schedule:work`
- Same environment variables as `web` (needs `DB_*` and `APP_KEY` to run
  the auto-close command and write to the database).

## 9. Seed the database (optional, for a demo)

From the `web` service's Shell (same place as step 4):

```
php artisan db:seed
```

Skip this for a real production launch - only run it for a demo/defense
environment where the fake students/items are useful to show off the
matching flow.

## 10. Final checklist before your demo

- [ ] Visit `https://your-app.up.railway.app` - should show the LostMate landing page.
- [ ] Register an account, report a lost item with a photo, confirm the
      photo actually displays (proves the volume is mounted correctly).
- [ ] Check the `worker` service's logs - a `RunItemMatching` job should
      appear and complete shortly after you submit a report.
- [ ] Register with a real school email and confirm the verification
      email arrives (check spam). If it doesn't, check the `worker` logs
      for a mail error, and set `REQUIRE_EMAIL_VERIFICATION=false` on
      `web` until it's fixed so new users aren't locked out.
- [ ] Log in as the seeded `admin@lostmate.test` account (if you seeded)
      and confirm `/admin` loads.
- [ ] `GET /up` should return a 200 - Railway uses this as the health
      check automatically.

## Costs

Railway isn't free forever - new accounts get trial credit, then it's
usage-based billing (a card on file is required once the trial credit
runs out). At this app's scale (three small services + a small MySQL
database, mostly idle between demo sessions) this typically runs a few
dollars a month. If that's not acceptable, the alternative is a
traditional cPanel shared host, which is free/cheap but can't run a real
persistent queue worker - jobs would process in batches via a cron-triggered
`queue:work --stop-when-empty` instead of continuously.
