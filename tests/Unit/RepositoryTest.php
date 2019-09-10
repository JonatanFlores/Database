<?php

namespace Tests\Unit;

use Database\Connection;
use Database\Criteria;
use Database\Filter;
use Database\Repository;
use Database\Transaction;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Mocks\Product;

class RepositoryTest extends TestCase
{
    /**
     * @before
     */
    public function beforeEach()
    {
        Connection::load(__DIR__.'/../config/database.php');
        Connection::open('sqlite')->query('CREATE TABLE IF NOT EXISTS products (id INTEGER PRIMARY_KEY, name TEXT NOT NULL, price REAL(10,2))');
        Connection::open('sqlite')->query("INSERT INTO products (id, name, price) VALUES (1, 'Product 01', 1000.50)");
        Connection::open('sqlite')->query("INSERT INTO products (id, name, price) VALUES (2, 'Product 02', 2100.00)");
        Connection::open('sqlite')->query("INSERT INTO products (id, name, price) VALUES (3, 'Product 03', 1699.00)");
    }

    /**
     * @after
     */
    public function afterEach()
    {
        Connection::open('sqlite')->query('DROP TABLE products');
    }

    public function testCanLoadListOfRecords()
    {
        Transaction::open('sqlite');
        $criteria = new Criteria;
        $repository = new Repository(new Product);
        $products = $repository->load($criteria);
        Transaction::close();

        $this->assertCount(3, $products);
    }

    public function testCanLoadRecordByPassingCriteriaAsArgument()
    {
        Transaction::open('sqlite');
        $criteria = new Criteria;
        $criteria->add(new Filter('id', '=', 2));
        $repository = new Repository(new Product);
        $products = $repository->load($criteria);
        Transaction::close();

        $this->assertCount(1, $products);
        $this->assertEquals(2, $products[0]->id);
        $this->assertEquals('Product 02', $products[0]->name);
    }

    public function testCanLoadRecordsAndOrderAndLimitThem()
    {
        Transaction::open('sqlite');
        $criteria = new Criteria;
        $criteria->setProperty('order', 'id DESC');
        $criteria->setProperty('limit', 2);
        $repository = new Repository(new Product);
        $products = $repository->load($criteria);
        Transaction::close();

        $this->assertCount(2, $products);
        $this->assertEquals(3, $products[0]->id);
        $this->assertEquals('Product 03', $products[0]->name);
        $this->assertEquals(2, $products[1]->id);
        $this->assertEquals('Product 02', $products[1]->name);
    }

    public function testCanLoadRecordsByAnOffset()
    {
        Transaction::open('sqlite');
        $criteria = new Criteria;
        $criteria->setProperty('order', 'id ASC');
        $criteria->setProperty('limit', 2);
        $criteria->setProperty('offset', 1);
        $repository = new Repository(new Product);
        $products = $repository->load($criteria);
        Transaction::close();

        $this->assertCount(2, $products);
        $this->assertEquals(2, $products[0]->id);
        $this->assertEquals('Product 02', $products[0]->name);
        $this->assertEquals(3, $products[1]->id);
        $this->assertEquals('Product 03', $products[1]->name);
    }
    
    public function testCanDeleteRecordsByCriteria()
    {
        Transaction::open('sqlite');
        $criteria = new Criteria;
        $criteria->add(new Filter('id', '=', 1));
        $product = new Repository(new Product);
        $product->delete($criteria);
        $products = new Repository(new Product);
        $products = $products->load(new Criteria);
        Transaction::close();

        $this->assertCount(2, $products);
    }

    public function testShouldReturnAnEmptyArrayWhenNoRecordsFound()
    {
        Transaction::open('sqlite');
        $criteria = new Criteria;
        $criteria->add(new Filter('id', '=', 100));
        $products = new Repository(new Product);
        $products = $products->load($criteria);
        Transaction::close();

        $this->assertIsArray($products);
        $this->assertCount(0, $products);
    }

    public function testCanCountRecordsByCriteria()
    {
        Transaction::open('sqlite');
        $products = new Repository(new Product);
        $products = $products->count(new Criteria);
        Transaction::close();

        $this->assertIsNumeric($products);
        $this->assertEquals(3, $products);
    }

    public function testCountRecordsShouldReturnZeroWhenNoRecordsFound()
    {
        Transaction::open('sqlite');
        $criteria = new Criteria;
        $criteria->add(new Filter('id', '=', 100));
        $products = new Repository(new Product);
        $products = $products->count($criteria);
        Transaction::close();

        $this->assertIsNumeric($products);
        $this->assertEquals(0, $products);
    }
}