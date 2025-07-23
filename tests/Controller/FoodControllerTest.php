<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class FoodControllerTest extends WebTestCase
{
    public function testPostFoodRequiresAuthentication(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/food/new');

        self::assertResponseStatusCodeSame(401);
    }

    public function testGetFoodRequiresAuthentication(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/food/1');

        self::assertResponseStatusCodeSame(401);
    }

    public function testPutFoodRequiresAuthentication(): void
    {
        $client = static::createClient();
        $client->request('PUT', '/api/food/1');

        self::assertResponseStatusCodeSame(401);
    }

    public function testDeleteFoodRequiresAuthentication(): void
    {
        $client = static::createClient();
        $client->request('DELETE', '/api/food/1');

        self::assertResponseStatusCodeSame(401);
    }
}
