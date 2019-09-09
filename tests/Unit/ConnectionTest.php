<?php

namespace Tests\Unit;

use Database\Connection;
use Database\Exceptions\ConfigFileNotFoundException;
use Database\Exceptions\InvalidConfigFileException;
use PDO;
use PHPUnit\Framework\TestCase;

class ConnectionTest extends TestCase
{
    public function testThrowExceptionWhenConfigPathInvalid()
    {
        $this->expectException(ConfigFileNotFoundException::class);

        Connection::load(__DIR__.'database.php');
    }

    public function testThrowsExceptionIfConfigFileContentsIsNotOfTypeArray()
    {
        $this->expectException(InvalidConfigFileException::class);

        Connection::load(__DIR__.'/../config/database-empty.php');
    }

    public function testConfigPathIsStoredWhenCorrectPathGiven()
    {
        Connection::load(__DIR__.'/../config/database.php');

        $this->assertEquals(__DIR__.'/../config/database.php', Connection::getPath());
    }

    public function testCanOpenConnectionToDatabase()
    {
        Connection::load(__DIR__.'/../config/database.php');

        $conn = Connection::open('sqlite');
        
        $this->assertInstanceOf(PDO::class, $conn);
    }
}