# Changelog

## 2.10.1-railway.1 (2026-11-24)

- First release: Faved 2.10.1 (`denho/faved:2.10.1@sha256:c2ef3f9f…f997`) with a first-boot admin account from template variables, `PORT` listener, `/healthz`, HTTPS-aware session cookies, sessions on the volume and a memory-sized Apache pool. Apache is forced to the prefork MPM at start (Railway builds can leave a second MPM enabled, AH00534).
