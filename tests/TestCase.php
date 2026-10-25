<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    protected function assertSafeTestDatabase(): void
    {
        // Inspect the URL-resolved connection without opening PDO or querying data.
        $connection = DB::connection();

        if (! $this->app->environment('testing')
            || $connection->getDriverName() !== 'mysql'
            || $connection->getDatabaseName() !== 'kaaklat_mysql_test') {
            throw new RuntimeException('Database refresh requires the testing environment and the resolved MySQL kaaklat_mysql_test database.');
        }
    }
}
