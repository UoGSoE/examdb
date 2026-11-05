<?php
namespace Deployer;

require 'recipe/laravel.php';

// Config
set('repository', 'git@versions.eng.gla.ac.uk:billy/exam-papers.git');

add('shared_files', []);
add('shared_dirs', []);
add('writable_dirs', []);

// Hosts
host('bugblatter.cose.gla.ac.uk')
    ->set('remote_user', 'deployer')
    ->set('deploy_path', '/var/www/examdb.cose.gla.ac.uk/');

// Tasks
// The build is disabled: the bundle committed in public/ is what ships, so
// production runs the same assets that were tested locally. This hook ran
// after the release went live, so a failed build would have gone unnoticed.
// task('build', function () {
//     run('cd {{release_path}} && npm install && npm run production');
// });

// Disable migrate for now (no .env on server yet)
task('artisan:migrate', function () {
    // disabled
});

// Hooks
after('deploy:failed', 'deploy:unlock');
// after('deploy:success', 'build');
