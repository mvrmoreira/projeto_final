<?php

namespace Uff\CalculatorBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class EnvironmentControllerTest extends WebTestCase
{
    public function testCompleteScenario()
    {
        // Create a new client to browse the application
        $client = static::createClient();

        // Create a new entry in the database
        $crawler = $client->request('GET', '/environment/');
        $this->assertEquals(200, $client->getResponse()->getStatusCode(), "Unexpected HTTP status code for GET /environment/");
        $crawler = $client->click($crawler->selectLink('Create a new entry')->link());

        // Fill in the form and submit it
        $form = $crawler->selectButton('Create')->form(array(
            'uff_calculatorbundle_environment[name]'  => 'Matheus Moreira',
            'uff_calculatorbundle_environment[maximumCost]'  => '1950',
            'uff_calculatorbundle_environment[minimumGflops]'  => '28792800',
            'uff_calculatorbundle_environment[totalRAM]'  => '213.5',
            'uff_calculatorbundle_environment[maximumDisk]'  => '142.3',
            'uff_calculatorbundle_environment[maximumTime]'  => '60',
            'uff_calculatorbundle_environment[maximumInstances]'  => '20',
            // ... other fields to fill
        ));

        $client->submit($form);
        $crawler = $client->followRedirect();

        // Check data in the show view
        $this->assertGreaterThan(0, $crawler->filter('td:contains("28792800")')->count(), 'Missing element td:contains("28792800")');

        // Edit the entity
        $crawler = $client->click($crawler->selectLink('Edit')->link());

        $form = $crawler->selectButton('Update')->form(array(
            'uff_calculatorbundle_environment[maximumCost]'  => '2000',
            // ... other fields to fill
        ));

        $client->submit($form);
        $crawler = $client->followRedirect();

        // Check the element contains an attribute with value equals "Foo"
        $this->assertGreaterThan(0, $crawler->filter('[value="2000"]')->count(), 'Missing element [value="Foo"]');

        // Delete the entity
        $client->submit($crawler->selectButton('Delete')->form());
        //$this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(302, $client->getResponse()->getStatusCode(), "Unexpected HTTP status code for GET /environment/");
        //$crawler = $client->followRedirect();

        // Check the entity has been delete on the list
        //$this->assertNotRegExp('/Foo/', $client->getResponse()->getContent());
    }
}
