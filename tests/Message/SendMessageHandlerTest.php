<?php

namespace App\Tests\Message;

use App\Enum\MessageStatusEnum;
use App\Message\SendMessage;
use App\Repository\MessageRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Messenger\MessageBusInterface;
use Zenstruck\Messenger\Test\InteractsWithMessenger;

class SendMessageHandlerTest extends KernelTestCase
{
    use InteractsWithMessenger;

    private readonly MessageRepository $messageRepository;
    private readonly MessageBusInterface $bus;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->messageRepository = self::getContainer()->get(MessageRepository::class);
        $this->bus = self::getContainer()->get(MessageBusInterface::class);
    }

    public function testMessageIsPersisted(): void
    {
        $text = 'Test text';

        $this->bus->dispatch(new SendMessage($text));

        $this->transport('sync')->process();

        $message = $this->messageRepository->findOneBy(['text' => $text]);

        $this->assertNotNull($message);
        $this->assertSame($text, $message->getText());
        $this->assertSame(MessageStatusEnum::Sent, $message->getStatus());
    }
}