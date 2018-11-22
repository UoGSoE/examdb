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

* `docker-compose -f docker-compose.dev.yml up`
* Go to `http://localhost:7172`
* Log in as 'admin' / 'secret'

### The full app

* Clone the repo
* Copy .env.example to .env and edit the settings to match your setup (DB host etc)
* Run `php artisan key:generate` to make an application key (this is used to encrypt the exam papers and 'sign' the secure URLs for external users)
* Run `php artisan migrate` to create all of the database tables
* Run `php artisan serve` to run the app for testing or point an apache/nginx config at `/path/to/code/public`.  For a real webserver you need to make sure the 'storage' and 'bootstrap/cache' are writable by the webserver user.
