<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class BookTest extends TestCase
{
    public function testGetQuery()
    {
        $this->get('/books/?id=1&name=2');

        $result = json_decode($this->response->getContent(), true);
        $exp = ['id' => 1, 'name' => 2];

        $this->assertEquals($exp, $result);
    }

    public function testUriParam()
    {
        $this->get('/books/1');
        $result = json_decode($this->response->getContent(), true);
        $this->assertEquals(['id' => 1], $result);
    }

    public function testPostJsonBody()
    {
        $this->post('/books/', ['name' => 'name1']);

        $result = json_decode($this->response->getContent(), true);
        $exp = ['name' => 'name1'];

        $this->assertEquals($exp, $result);
    }

    // public function testPostFormBody()
    // {
    //     //
    // }

    // public function testPostFileBody()
    // {
    //     //
    // }
}
