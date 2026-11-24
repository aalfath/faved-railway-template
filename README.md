# Faved on Railway

[![Deploy on Railway](https://railway.com/button.svg)](https://railway.com/new/template/faved-1?utm_medium=integration&utm_source=button&utm_campaign=faved)

Railway template for [Faved](https://github.com/denho/faved), a fast, lightweight self-hosted bookmark manager with nested tags, previews and browser import.

**Version:** Faved 2.10.1 (`denho/faved:2.10.1@sha256:c2ef3f9f45608054f7d28f3e6757c68d307938bcc26376c9ad4b48d77462f997`).

## What it deploys

One service, **Faved** (PHP 8.5 + Apache from the official image), built from this repo's `Dockerfile`, with one Railway volume at `/var/www/html/storage` that holds the SQLite database, preview images and login sessions.

What this wrapper adds to the official image:

- **Locked from the first second.** Faved has no login until a user exists, and its setup API is open, so whoever opened a fresh deploy first could take it over. Here the start script creates the database (with Faved's welcome bookmarks) and the admin account from template variables before the web server starts. If that fails, the service stops instead of serving an open app.
- Apache listens on Railway's `PORT` and runs under a small supervisor that exits non-zero if Apache ever stops, so Railway restarts it; `/healthz` answers 200 once the database and the admin account exist.
- HTTPS from Railway's proxy is honoured, so the session cookie is `Secure`.
- PHP sessions are kept on the volume, so you stay logged in across redeploys.
- The Apache worker pool is sized from the service's memory limit (2 to 16 workers), so it fits Railway's 0.5 GB Free plan.

## Variables

| Variable | Default | Purpose |
|---|---|---|
| `FAVED_ADMIN_USERNAME` | `admin` | Admin login, created on the first boot only (letters, digits, underscores). |
| `FAVED_ADMIN_PASSWORD` | `${{secret(24)}}` | Admin password, created on the first boot only. Read it in the service's **Variables** tab. |
| `PORT` | `8080` | Port Apache listens on. |

Changing the two admin variables after the first boot does nothing: change the username or password in Faved under **Settings → Authentication**.

## After deploying

1. Open the service's public URL.
2. Log in with `FAVED_ADMIN_USERNAME` and `FAVED_ADMIN_PASSWORD` from the service's Variables tab.
3. Change the password under **Settings → Authentication** if you like, and import your browser bookmarks under **Settings → Import**.

## Memory

Faved fits Railway's Free plan: tested with a 0.5 GB / 1 vCPU limit, about 30 MB of app memory idle and under 90 MB at peak with 40 parallel requests, 5 Apache workers.

## Updates

This template builds from this GitHub repo. When we bump the pinned Faved version on `main`, Railway shows deployers an "update available" notice; read [CHANGELOG.md](CHANGELOG.md) before applying it. Your data stays on the volume.

## Licence

Faved is MIT-licensed by its authors. This wrapper is MIT too.
