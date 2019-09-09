<?php

namespace Tests\Unit\Database;

use Database\Connection;
use Database\Transaction;
use PDO;
use PHPUnit\Framework\TestCase;

class TransactionTest extends TestCase
{
    /**
     * @before
     */
    public function beforeEach()
    {
        Connection::load(__DIR__.'/../config/database.php');
        Connection::open('sqlite')->query('CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY_KEY, name TEXT NOT NULL)');
    }

    /**
     * @after
     */
    public function afterEach()
    {
        Connection::open('sqlite')->query('DROP TABLE users');
    }

    public function testCanOpenTransactionToDatabase()
    {
        Transaction::open('sqlite');

        $this->assertInstanceOf(PDO::class, Transaction::get());
    }

    public function testCanCommitDatabaseChanges()
    {
        Transaction::open('sqlite');
        Transaction::get()->query("INSERT INTO users (id, name) VALUES (1, 'John Doe')");
        Transaction::get()->query("INSERT INTO users (id, name) VALUES (2, 'Jane Doe')");
        Transaction::close();

        Transaction::open('sqlite');
        $stmt = Transaction::get()->query("SELECT * FROM users");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        Transaction::close();
        $this->assertCount(2, $users);
    }

    public function testCanRollbackDatabaseChanges()
    {
        Transaction::open('sqlite');
        Transaction::get()->query("INSERT INTO users (id, name) VALUES (1, 'John Doe')");
        Transaction::get()->query("INSERT INTO users (id, name) VALUES (2, 'Jane Doe')");
        Transaction::rollback();

        Transaction::open('sqlite');
        $stmt = Transaction::get()->query("SELECT * FROM users");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        Transaction::close();
        $this->assertCount(0, $users);
    }
}