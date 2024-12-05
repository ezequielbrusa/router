<?php

namespace Test\Unit;

use Garcia\Router;
use PHPUnit\Framework\TestCase;

class Test {
    public function index(){
        return ['Hello' => 'index'];
    }
    public function store(){
        return ['Hello' => 'store'];
    }
    public function show(){
        return ['Hello' => 'show'];
    }
    public function update($params){
        return ['Hello' => "update".$params['id']];
    }
    public function destroy(){
        return ['Hello' => 'destroy'];
    }
}

class RouterTest extends TestCase
{
    /** @test - Test if the route is added to the routes array */
    public function addRoute()
    {
        Router::addRoute('GET', '/test', fn () => 'test');

        $this->assertIsArray(Router::getRoutes());
        $this->assertCount(1, Router::getRoutes());
    }

    /** @test - Test if the route is added to the routes array */
    public function testGet()
    {
        Router::get('/test', fn () => 'test');

        $this->assertIsArray(Router::getRoutes());
        $this->assertCount(2, Router::getRoutes());
    }

    /** @test - Test if the route is added to the routes array */
    public function testPost()
    {
        Router::post('/test', fn () => 'test');

        $this->assertIsArray(Router::getRoutes());
        $this->assertCount(3, Router::getRoutes());
    }

    /** @test - Test if the route is added to the routes array */
    public function testPut()
    {
        Router::put('/test', fn () => 'test');

        $this->assertIsArray(Router::getRoutes());
        $this->assertCount(4, Router::getRoutes());
    }

    /** @test - test the json response from the router */
    public function testJsonResponse()
    {
        Router::get('/test', fn () => ['id' => 1, 'name' => 'John Doe', 'email' => '', 'phone' => '']);

        $this->assertIsArray(Router::getRoutes());
        $this->assertCount(5, Router::getRoutes());
    }
}