<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasTable('roles')) {
            return;
        }

        foreach (['admin', 'customer', 'analyst'] as $role) {
            Role::findOrCreate($role, 'web');
        }
    }
}
