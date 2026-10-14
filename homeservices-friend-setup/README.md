# HomeServices — friend setup

Install Docker Desktop (Windows: Linux containers) or Docker Engine with Docker Compose 2.24 or newer. Start Docker before continuing.

Extract the shared ZIP, then open a terminal in the extracted folder containing compose.yaml and compose.hub.yaml. No application source, PHP, Composer, or local MySQL installation is needed. The application is downloaded from Docker Hub.

Run each command separately. Stop if a command fails.

## First setup

Download the images:

```sh
docker compose -f compose.yaml -f compose.hub.yaml pull
```

Generate your own private settings. Run this only if .env.docker does not already exist; never overwrite it or share it:

```sh
docker compose -f compose.yaml -f compose.hub.yaml run --rm --no-deps --no-build --entrypoint php --volume "./:/setup" app docker/init.php /setup/.env.docker
```

Start the services:

```sh
docker compose -f compose.yaml -f compose.hub.yaml up -d --no-build --wait
```

Initialize your Docker database:

```sh
docker compose -f compose.yaml -f compose.hub.yaml exec --user www-data app php artisan migrate --force
docker compose -f compose.yaml -f compose.hub.yaml exec --user www-data app php artisan db:seed --class=ServiceCategorySeeder --force
```

Open http://localhost:8000 for the app. Register a test account, then open http://localhost:8025 to read its verification email and OTP. Emails are captured locally rather than delivered to real inboxes.

Each computer has its own database; the publisher's existing users and data are not included. This is a development/testing setup.

## Later use

Start again:

```sh
docker compose -f compose.yaml -f compose.hub.yaml up -d --no-build --wait
```

Stop:

```sh
docker compose -f compose.yaml -f compose.hub.yaml stop
```

Keep .env.docker and the Docker volumes. Do not use down --volumes or database reset commands: they delete saved data.

If startup fails, check service status:

```sh
docker compose -f compose.yaml -f compose.hub.yaml ps
```

The image must be accessible on Docker Hub (public, or shared with your account). A private repository requires docker login and access. If Docker reports a platform mismatch, ask the publisher for an image supporting your computer's architecture.
