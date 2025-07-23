<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CategoryControllerTest extends WebTestCase
{
    public function testPostCategoryRequiresAuthentication(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/category/new');

        self::assertResponseStatusCodeSame(401);
    }

    public function testGetCategoryRequiresAuthentication(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/category/1');

        self::assertResponseStatusCodeSame(401);
    }

    public function testPutCategoryRequiresAuthentication(): void
    {
        $client = static::createClient();
        $client->request('PUT', '/api/category/1');

        self::assertResponseStatusCodeSame(401);
    }

    public function testDeleteCategoryRequiresAuthentication(): void
    {
        $client = static::createClient();
        $client->request('DELETE', '/api/category/1');

        self::assertResponseStatusCodeSame(401);
    }
}
