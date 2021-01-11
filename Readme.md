# Exam Papers System

A web app to handle the process of setting exam papers.

Each course has setters, moderators and externals.  The setter 'sets' the paper and the
moderator can add revisions, notes etc.  When they have agreed the exam paper is ok, the
external examiner is notified and they can add their own comments, revisions etc.  The process
is repeated until everyone is happy and then the paper is approved and can be used in for
the real examination.


All uploaded exam papers are encrypted on disk and only people who are involved with the
specific course can download them.

The system sends email notifications to the involved people when something happens - eg, if
a moderator leaves some comments on a paper, the setters get an email letting them know.

Admin users can allocate staff to courses as setters, moderators or externals.  They can also
create users if they haven't logged in before to 'pre-allocate' them.

External users log in via a secure time-limited URL which is emailed to them when they enter
their email address into the login page.  This saves having local passwords for external
users.

## Installation

### Docker to try it out

```sh
export IMAGE_NAME=examdb:1.0
docker build --target=ci -t ${IMAGE_NAME} .
docker-compose up
```
Wait until things all seem to be running, then in another terminal in the same directory, run
```sh
docker-compose exec app php artisan db:seed --class=TestDataSeeder
```
Now go to http://localhost:3000 and log in as 'admin' / 'secret'.

### The full app

* Clone the repo
* Copy .env.example to .env and edit the settings to match your setup (DB host etc)
* Run `php artisan key:generate` to make an application key (this is used to encrypt the exam papers and 'sign' the secure URLs for external users)
* Run `php artisan migrate` to create all of the database tables
* Run `php artisan serve` to run the app for testing or point an apache/nginx config at `/path/to/code/public`.  For a real webserver you need to make sure the 'storage' and 'bootstrap/cache' are writable by the webserver user.

### File uploads
The system assumes there is a 'minio' server running (note: the `docker-compose` setup creates one by itself).  There is a `minio_dev.sh` script in the root of the repo
which will start a docker container running minio.  You can also steal the api/secret keys from that to put
in your .env for dev.  Once minio is running you'll have to log in via the web (localhost:9000) with the
secret keys from the script and click the button at the bottom right of the screen to add a new 'bucket' called
'exampapers'.  Then you should be good to go.
