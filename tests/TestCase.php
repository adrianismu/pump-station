<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use DatabaseTransactions; // Menggunakan database transactions untuk MySQL
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Ensure database exists for testing
        $this->artisan('migrate:fresh');
    }
}
