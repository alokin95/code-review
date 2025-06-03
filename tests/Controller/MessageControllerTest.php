<?php

declare(strict_types=1);

namespace Controller;

use App\Enum\MessageStatusEnum;
use App\Message\SendMessage;
use App\Tests\Factory\MessageFactory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Messenger\Test\InteractsWithMessenger;

class MessageControllerTest extends WebTestCase
{
    use InteractsWithMessenger;

    public function test_list(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        $em = $container->get('doctrine')->getManager();
        $em->createQuery('DELETE FROM App\Entity\Message')->execute();

        $factory = new MessageFactory($em);
        $factory->create('Text');

        $client->request('GET', '/messages');

        $this->assertResponseIsSuccessful();

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('messages', $data);
        $this->assertCount(1, $data['messages']);

        $messageData = $data['messages'][0];
        $this->assertArrayHasKey('text', $messageData);
        $this->assertSame('Text', $messageData['text']);
        $this->assertArrayHasKey('status', $messageData);
        $this->assertSame(MessageStatusEnum::Sent->value, $messageData['status']);
    }

    public function test_list_returns_empty_when_no_messages(): void
    {
        $client = static::createClient();
        static::getContainer()->get('doctrine')->getManager()
            ->createQuery('DELETE FROM App\Entity\Message')->execute();

        $client->request('GET', '/messages');

        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('messages', $data);
        $this->assertCount(0, $data['messages']);
    }

    public function test_list_with_invalid_status_filter(): void
    {
        $client = static::createClient();
        $client->request('GET', '/messages?status=INVALID');

        $this->assertResponseStatusCodeSame(400);
    }


    function test_that_it_sends_a_message(): void
    {
        $client = static::createClient();
        $client->request('GET', '/messages/send', [
            'text' => 'Hello World',
        ]);

        $this->assertResponseIsSuccessful();
        // This is using https://packagist.org/packages/zenstruck/messenger-test
        $this->transport('sync')
            ->queue()
            ->assertContains(SendMessage::class, 1);
    }
}
