<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (isset($this->app)) {
            $this->app->forgetScopedInstances();
        }
    }

    protected function tearDown(): void
    {
        if (isset($this->app)) {
            $this->app->forgetScopedInstances();
        }

        if (isset($this->app) && $this->app->bound('session')) {
            $this->app['session']->flush();
        }

        parent::tearDown();
    }
}
