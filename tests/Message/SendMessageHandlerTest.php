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

    private MessageRepository $messageRepository;
    private MessageBusInterface $bus;

    protected function setUp(): void
    {
        self::bootKernel();

        /** @var MessageRepository $messagesRepository */
        $messagesRepository = self::getContainer()->get(MessageRepository::class);
        $this->messageRepository = $messagesRepository;

        /** @var MessageBusInterface $bus */
        $bus = self::getContainer()->get(MessageBusInterface::class);
        $this->bus = $bus;
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