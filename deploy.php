<?php

namespace Deployer;

require 'recipe/common.php';

// Laptop: composer install && source .env.deploy && vendor/bin/dep deploy production
// First time on VM: vendor/bin/dep deploy:setup production  (creates shared/ + releases/)

set('application', 'e-commerce-app');
set('keep_releases', 3);
set('git_tty', true);
set('ssh_multiplexing', true);

set('repository', function (): string {
    $repo = getenv('DEPLOY_REPOSITORY') ?: '';

    if ($repo !== '') {
        return $repo;
    }

    throw new \RuntimeException(
        'Set DEPLOY_REPOSITORY (SSH URL the VM can clone), e.g. git@github.com:org/e-commerce-app.git'
    );
});

set('branch', getenv('DEPLOY_BRANCH') ?: 'main');

set('shared_files', [
    'auth/.env',
    'notification/.env',
    'catalog/.env',
    'order/.env',
    'search/.env',
    'analytics/.env',
    'recommendation/.env',
]);

set('writable_dirs', []);
set('writable_mode', 'skip');

set('composer_action', 'install');

host('production')
    ->setHostname(getenv('DEPLOY_HOST') ?: '127.0.0.1')
    ->setRemoteUser(getenv('DEPLOY_USER') ?: 'ubuntu')
    ->setDeployPath(getenv('DEPLOY_PATH') ?: '~/e-commerce-app');

desc('Copy shared .env into current release (Docker bind mounts cannot use Deployer symlinks)');
task('app:materialize:env', function (): void {
    foreach (get('shared_files') as $relative) {
        $shared = '{{deploy_path}}/shared/'.$relative;
        $current = '{{deploy_path}}/current/'.$relative;

        run(<<<BASH
set -e
if [ ! -f "{$shared}" ]; then
  echo "Skip {$relative}: missing {$shared}" >&2
  exit 0
fi
if [ -L "{$current}" ] || [ ! -f "{$current}" ]; then
  rm -f "{$current}"
  cp "{$shared}" "{$current}"
fi
BASH);
    }
});

desc('Ensure Docker network exists');
task('app:docker:network', function (): void {
    run('docker network inspect microservices-net >/dev/null 2>&1 || docker network create microservices-net');
});

desc('Build and start full stack from current release');
task('app:docker:up', function (): void {
    cd('{{deploy_path}}/current');
    run('chmod +x scripts/fullstack.sh');
    // First fullstack build can take 30+ minutes; disable Deployer SSH timeout.
    run('./scripts/fullstack.sh up', [
        'timeout' => 0,
        'idle_timeout' => 0,
    ]);
});

desc('Deploy monorepo with Docker Compose');
// deploy:prepare already runs lock, release, update_code, shared, writable — do not repeat.
task('deploy', [
    'deploy:prepare',
    'deploy:symlink',
    'app:materialize:env',
    'app:docker:network',
    'app:docker:up',
    'deploy:unlock',
    'deploy:cleanup',
    'deploy:success',
]);

after('deploy:failed', 'deploy:unlock');
