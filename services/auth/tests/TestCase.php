<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        putenv('APP_ENV=testing');
        $_ENV['APP_ENV'] = 'testing';
        $_SERVER['APP_ENV'] = 'testing';

        return parent::createApplication();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->app['auth']->shouldUse('web');
        $this->withoutVite();

        if (! Schema::hasTable('roles')) {
            return;
        }

        foreach (['admin', 'customer', 'analyst'] as $role) {
            Role::findOrCreate($role, 'web');
        }
    }
}
