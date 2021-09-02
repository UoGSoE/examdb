# ExamDB

This is a system for allowing academics to upload, comment and moderate exam papers.  There is also a system of checklists they have to follow to indicate that the correct procedures have been followed and sign off on each stage of the paper.

## Running a quick demo

If you have Docker installed you should be able to clone this project then just run :
```bash
export IMAGE_NAME=examdb:demo
docker build -t ${IMAGE_NAME} .
docker-compose up -d  # wait a little bit to see everything is running
docker-compose exec app php artisan examdb:createadmin admin admin@example.com Admin Smith
```
Then you should be able to log in as 'admin' with any password at http://localhost:3000 .

## Running production single-server docker swarm

There is an example settings file in `.env.single-server` - edit the values in there to match your own setup.  There is also an example deployment script in `single-deploy.sh` - you can edit that to set the container image name and hostname you want to use then just run `./single-deploy.sh`.

Note that the example scripts make some assumptions about your swarm setup.  In particular that you have an overlay network called 'proxy' and traefik v2 running in it.  Please see https://github.com/UoGSoE/ansible_docker_traefik for an example ansible playbook that would set up docker the way this assumes.

By default it will run in 'demo mode' where it spins up a throw-away copy of mysql and minio inside docker.  Assuming you don't want that in production remove the `,single-server-stack-demo.yml` from the script `docker stack deploy...` line.

Make particular note of the `APP_KEY` in the `.env.single-server` file.  This is used internally by the code - but is especially important as it is also used to encrypt the contents of all the exam paper uploads.  To generate a new random key you can do something like :
```bash
docker run --rm uogsoe/examdb-test:0.2 php artisan key:generate --show
```
(with your own container name rather than the uogsoe one)

## Running on a multi-node Swarm

This is a little out-of scope for a readme - but you can see an example swarm stack in `prod-stack.yml` and a gitlab-ci deployment script in `.gitlab-ci.yml`.

## Running in a 'bare metal' install

As there are too many variations to consider - please just have a read of the [Laravel Deployment Guide](https://laravel.com/docs/8.x/deployment) and adjust to your own setup.
