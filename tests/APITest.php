<?php
 
class APITest extends PHPUnit_Framework_TestCase{
    private $api;
    private $sample_category;

    public function setUp(){
        $this->api = new TiendaNube\API(1234, 'abcdefabcdef', 'Test App');

        $this->sample_category = '{
            "description": {
                "en": "",
                "es": "",
                "pt": ""
            },
            "handle": {
                "en": "poke-balls",
                "es": "poke-balls",
                "pt": "poke-balls"
            },
            "id": 4567,
            "name": {
                "en": "Poke Balls",
                "es": "Poke Balls",
                "pt": "Poke Balls"
            },
            "parent": null,
            "subcategories": [],
            "created_at": "2013-01-03T09:11:51-03:00",
            "updated_at": "2013-03-11T09:14:11-03:00"
        }';
    }

    public function testGet(){
        $mock = new MockRequests($this->sample_category, 200, ['X-Main-Language' => 'es']);
        $this->api->requests = $mock;

        $response = $this->api->get('categories/4567');
        $this->assertEquals('https://api.tiendanube.com/v1/1234/categories/4567', $mock->args[0]);
        $this->assertEquals('GET', $mock->args[3]);

        $this->assertEquals(200, $response->status_code);
        $this->assertEquals(4567, $response->body->id);

        $this->assertTrue(isset($response->headers['X-Main-Language']));
        $this->assertEquals('es', $response->headers['X-Main-Language']);
    }

    public function testPost(){
        $mock = new MockRequests($this->sample_category, 201, ['X-Main-Language' => 'es']);
        $this->api->requests = $mock;

        $response = $this->api->post('categories', ['name' => 'Poke Balls']);
        $this->assertEquals('https://api.tiendanube.com/v1/1234/categories', $mock->args[0]);
        $this->assertEquals('{"name":"Poke Balls"}', $mock->args[2]);
        $this->assertEquals('POST', $mock->args[3]);

        $this->assertEquals(201, $response->status_code);
        $this->assertEquals(4567, $response->body->id);

        $this->assertTrue(isset($response->headers['X-Main-Language']));
        $this->assertEquals('es', $response->headers['X-Main-Language']);
    }

    public function testPut(){
        $mock = new MockRequests($this->sample_category, 200, ['X-Main-Language' => 'es']);
        $this->api->requests = $mock;

        $response = $this->api->put('categories/4567', ['name' => 'Poke Balls']);
        $this->assertEquals('https://api.tiendanube.com/v1/1234/categories/4567', $mock->args[0]);
        $this->assertEquals('{"name":"Poke Balls"}', $mock->args[2]);
        $this->assertEquals('PUT', $mock->args[3]);

        $this->assertEquals(200, $response->status_code);
        $this->assertEquals(4567, $response->body->id);

        $this->assertTrue(isset($response->headers['X-Main-Language']));
        $this->assertEquals('es', $response->headers['X-Main-Language']);
    }

    public function testDelete(){
        $mock = new MockRequests('{}', 200);
        $this->api->requests = $mock;

        $response = $this->api->delete('categories/4567');
        $this->assertEquals('https://api.tiendanube.com/v1/1234/categories/4567', $mock->args[0]);
        $this->assertEquals('DELETE', $mock->args[3]);

        $this->assertEquals(200, $response->status_code);
        $this->assertEquals(0, count((array) $response->body));
    }

    public function testPagination(){
        $mock = new MockRequests('[]', 200, [
            'X-Main-Language' => 'es',
            'Link' => '<https://api.tiendanube.com/v1/1234/products?page=1>; rel="first", '.
                      '<https://api.tiendanube.com/v1/1234/products?page=4>; rel="prev", '.
                      '<https://api.tiendanube.com/v1/1234/products?page=6>; rel="next", '.
                      '<https://api.tiendanube.com/v1/1234/products?page=10>; rel="last"',
        ]);

        $this->api->requests = $mock;
        $response = $this->api->get('products', ['page' => 5]);
        $this->assertEquals('https://api.tiendanube.com/v1/1234/products?page=5', $mock->args[0]);

        $response->first();
        $this->assertEquals('https://api.tiendanube.com/v1/1234/products?page=1', $mock->args[0]);

        $response->prev();
        $this->assertEquals('https://api.tiendanube.com/v1/1234/products?page=4', $mock->args[0]);

        $response->next();
        $this->assertEquals('https://api.tiendanube.com/v1/1234/products?page=6', $mock->args[0]);

        $response->last();
        $this->assertEquals('https://api.tiendanube.com/v1/1234/products?page=10', $mock->args[0]);
    }

    public function testHeaders(){
        $mock = new MockRequests('[]', 200, ['X-Main-Language' => 'es']);
        $this->api->requests = $mock;

        $response = $this->api->get('categories');

        $this->assertArrayHasKey('Authentication', $mock->args[1]);
        $this->assertEquals('bearer abcdefabcdef', $mock->args[1]['Authentication']);
        
        $this->assertArrayHasKey('Content-Type', $mock->args[1]);
        $this->assertEquals('application/json', $mock->args[1]['Content-Type']);

        $this->assertArrayHasKey('useragent', $mock->args[4]);
        $this->assertEquals('Test App', $mock->args[4]['useragent']);
    }

    /**
     * @expectedException TiendaNube\API\Exception
     */
    public function testError(){
        $this->api->requests = new MockRequests('{"code": 401, "message": "Unauthorized", "description": "Invalid access token"}', 401);
        $this->api->get('categories/4567');
    }

    /**
     * @expectedException TiendaNube\API\NotFoundException
     */
    public function test404(){
        $this->api->requests = new MockRequests('{"code": 404, "message": "Not Found", "description": "Category with such id does not exist"}', 404);
        $this->api->get('categories/4568');
    }
}