<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BloggerControllerTest extends WebTestCase
{
    public function testBlogsListReturns200(): void
    {
        $client = static::createClient();
        $client->request('GET', '/blogs');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('nav.navbar');
    }

    public function testCreatePostRedirectsToLoginWhenUnauthenticated(): void
    {
        $client = static::createClient();
        $client->request('GET', '/blogger');

        $this->assertResponseRedirects('/login');
    }

    public function testMyEntriesRedirectsToLoginWhenUnauthenticated(): void
    {
        $client = static::createClient();
        $client->request('GET', '/entradas');

        $this->assertResponseRedirects('/login');
    }
}
