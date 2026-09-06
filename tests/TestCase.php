<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Feature tests render Inertia's app.blade.php, which calls @vite().
        // CI runs the backend suite without building assets, so there is no
        // public/build/manifest.json. Disable the Vite directives for tests.
        $this->withoutVite();
    }
}
