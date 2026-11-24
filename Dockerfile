# Faved 2.10.1, official image, pinned by version and digest.
FROM denho/faved:2.10.1@sha256:c2ef3f9f45608054f7d28f3e6757c68d307938bcc26376c9ad4b48d77462f997

# Railway wrapper: listen on $PORT, honour Railway's HTTPS proxy, keep PHP sessions on
# the volume, small Apache worker pool for 0.5 GB, /healthz, and a first-boot bootstrap
# that creates the database and the admin account from template variables.
COPY rootfs/ /
RUN set -eux; \
    a2dismod -f ssl >/dev/null; \
    a2enmod headers >/dev/null; \
    rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.*; \
    test -e /etc/apache2/mods-enabled/mpm_prefork.load; \
    command -v setpriv; \
    chmod 0755 /usr/local/bin/faved-railway-start; \
    php -l /opt/faved-railway/bootstrap.php; php -l /var/www/html/public/healthz.php

ENV PORT=8080
EXPOSE 8080
CMD ["/usr/local/bin/faved-railway-start"]
