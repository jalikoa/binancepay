<?php
namespace Tests\Unit\Utils;

use App\Utils\UUID;
use Tests\TestCase;

class UUIDTest extends TestCase
{
    public function test_uuid_v4_generates_valid_format()
    {
        $uuid = UUID::v4();
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $uuid);
    }

    public function test_multiple_calls_generate_different_uuids()
    {
        $uuid1 = UUID::v4();
        $uuid2 = UUID::v4();
        $this->assertNotEquals($uuid1, $uuid2);
    }
}