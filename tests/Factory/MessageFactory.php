<?php

namespace App\Tests\Factory;

use App\Entity\Message;
use App\Enum\MessageStatusEnum;
use Doctrine\ORM\EntityManagerInterface;

final readonly class MessageFactory
{
    public function __construct(private EntityManagerInterface $em)
    {}

    public function create(
        string $text = 'Generated text',
        MessageStatusEnum $status = MessageStatusEnum::Sent
    ): Message
    {
        $message = new Message();
        $message->setText($text);
        $message->setStatus($status);

        $this->em->persist($message);
        $this->em->flush();

        return $message;
    }
}