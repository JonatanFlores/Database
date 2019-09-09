<?php

namespace Tests\Unit;

use Database\Record;
use Database\Connection;
use Database\Transaction;
use PHPUnit\Framework\TestCase;

class RecordTest extends TestCase
{
    /**
     * @before
     */
    public function beforeEach()
    {
        Connection::load(__DIR__.'/../config/database.php');
        Connection::open('sqlite')->query('CREATE TABLE IF NOT EXISTS products (id INTEGER PRIMARY_KEY, name TEXT NOT NULL)');
        Connection::open('sqlite')->query("INSERT INTO products (id, name) VALUES (1, 'Product 01')");
        Connection::open('sqlite')->query("INSERT INTO products (id, name) VALUES (2, 'Product 02')");
        Connection::open('sqlite')->query("INSERT INTO products (id, name) VALUES (3, 'Product 03')");
    }

    /**
     * @after
     */
    public function afterEach()
    {
        Connection::open('sqlite')->query('DROP TABLE products');
    }

    public function testCanGetTheTableThatTheClassRepresents()
    {
        $this->assertEquals('products', (new Product)->getEntity());
    }

    public function testCanGetThePrimaryKeyField()
    {
        $this->assertEquals('id', (new Product)->getPrimaryKey());
    }

    public function testCanLoadDatabaseRecordByIdAndPopulateRecordClass()
    {
        Transaction::open('sqlite');
        $product = (new Product)->load(1);
        Transaction::close();

        $this->assertEquals(1, $product->id);
        $this->assertEquals('Product 01', $product->name);
    }

    public function testCanFillObjectPropertiesByPassingAnArray()
    {
        $data = ['id' => 1, 'name' => 'Test Product'];

        $product = new Product;
        $product->fromArray($data);

        $this->assertEquals($data['id'], $product->id);
        $this->assertEquals($data['name'], $product->name);
    }

    public function testCanGetObjectPropertiesAsArray()
    {
        $data = ['id' => 1, 'name' => 'Test Product'];

        $product = new Product;
        $product->fromArray($data);
        $dataFrom = $product->toArray();

        $this->assertArrayHasKey('id', $dataFrom);
        $this->assertArrayHasKey('name', $dataFrom);
    }
}

class Product extends Record
{
    const TABLE_NAME = 'products';
}

