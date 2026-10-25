<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;

class TestDatabaseSafetyTest extends TestCase
{
    // These checks never use RefreshDatabase or query either database.
    public function test_guard_accepts_the_resolved_dedicated_test_database(): void
    {
        $this->assertSafeTestDatabase();
        $this->assertTrue(true);
    }

    public function test_guard_rejects_application_database_despite_test_environment_variables(): void
    {
        config(['database.connections.mysql.database' => 'kaaklat']);
        DB::purge('mysql');
        $this->expectException(RuntimeException::class);
        $this->assertSafeTestDatabase();
    }

    public function test_guard_rejects_a_url_that_overrides_the_test_database(): void
    {
        config(['database.connections.mysql.url' => 'mysql://unused:unused@127.0.0.1:3307/kaaklat']);
        DB::purge('mysql');
        $this->expectException(RuntimeException::class);
        $this->assertSafeTestDatabase();
    }
}
