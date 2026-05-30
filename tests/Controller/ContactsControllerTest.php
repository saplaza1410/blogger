<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ContactsControllerTest extends WebTestCase
{
    public function testContactFormRendersCorrectly(): void
    {
        $client = static::createClient();
        $client->request('GET', '/contacts');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testContactFormSubmitWithValidDataRedirects(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/contacts');

        $form = $crawler->selectButton('Registrar')->form([
            'contact[name]'    => 'Test User',
            'contact[email]'   => 'test@example.com',
            'contact[message]' => 'Este es un mensaje de prueba',
        ]);

        $client->submit($form);

        $this->assertResponseRedirects();
    }
}
