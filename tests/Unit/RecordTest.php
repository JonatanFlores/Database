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

    public function testCanInstanciateRecordAndLoadTheDataById()
    {
        Transaction::open('sqlite');
        $product = new Product(1);
        Transaction::close();

        $this->assertEquals(1, $product->id);
        $this->assertEquals('Product 01', $product->name);
    }

    public function testCanLoadRecordUsingTheFindMethod()
    {
        Transaction::open('sqlite');
        $product = (new Product)->find(1);
        Transaction::close();

        $this->assertEquals(1, $product->id);
        $this->assertEquals('Product 01', $product->name);
    }

    public function testCanEscapeValues()
    {
        $someString = "Some String Value";
        $escapedString = "'".\addslashes($someString)."'";
        $product = new Product;

        $this->assertEquals($escapedString, $product->escape($someString));
        $this->assertEquals('TRUE', $product->escape(true));
        $this->assertEquals('FALSE', $product->escape(false));
        $this->assertEquals(12.50, $product->escape(12.50));
        $this->assertEquals(null, $product->escape(null));
        $this->assertEquals('NULL', $product->escape(''));
    }

    public function testCanPrepareValues()
    {
        $someString = "Some String Value";
        $escapedString = "'".\addslashes($someString)."'";
        $data = [
            'some_string' => $someString,
            'bool_true' => true,
            'bool_false' => false,
            'price' => 12.50,
            'null_field' => null,
            'empty_field' => '',
        ];

        $product = new Product;
        $prepared = $product->prepare($data);

        $this->assertEquals($escapedString, $prepared['some_string']);
        $this->assertEquals('TRUE', $prepared['bool_true']);
        $this->assertEquals('FALSE', $prepared['bool_false']);
        $this->assertEquals(12.50, $prepared['price']);
        $this->assertTrue(!isset($prepared['null_field']));
        $this->assertEquals('NULL', $prepared['empty_field']);
    }

    public function testCanInsertDataUsingTheStoreMethod()
    {
        Transaction::open('sqlite');
        $product = new Product;
        $product->name = 'Product 04';
        $product->store();
        Transaction::close();

        $this->assertEquals(4, $product->id);
        $this->assertEquals('Product 04', $product->name);
    }

    public function testCanUpdatedDataUsingTheStoreMethod()
    {
        Transaction::open('sqlite');
        $product = new Product(3);
        $product->name = 'Product 03 - UPDATED';
        $product->store();
        Transaction::close();

        $this->assertEquals(3, $product->id);
        $this->assertEquals('Product 03 - UPDATED', $product->name);
    }

    public function testCanDeleteRecordFromTheDatabase()
    {
        Transaction::open('sqlite');
        $id = 1;
        (new Product)->delete($id);
        $product = new Product($id);
        Transaction::close();

        $this->assertNull($product->id);
        $this->assertNull($product->name);
    }
}

class Product extends Record
{
    const TABLE_NAME = 'products';
}

