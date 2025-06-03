<?php
declare(strict_types=1);

namespace App\Message;

use App\Entity\Message;
use App\Enum\MessageStatusEnum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
/**
 * TODO: Cover with a test
 */
class SendMessageHandler
{
    public function __construct(private EntityManagerInterface $manager)
    {
    }
    
    public function __invoke(SendMessage $sendMessage): void
    {
        $message = new Message();
        $message->setText($sendMessage->text);
        $message->setStatus(MessageStatusEnum::Sent);

        $this->manager->persist($message);
        $this->manager->flush();
    }
}