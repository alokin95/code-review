<?php

namespace App\ViewModel;

use App\Entity\Message;

final readonly class MessageView
{
    public function __construct(
        public string $uuid,
        public string $text,
        public ?string $status,
    ) {}

    public static function fromEntity(Message $message): self
    {
        return new self(
            $message->getUuid()->toRfc4122(),
            $message->getText(),
            $message->getStatus()?->value,
        );
    }
}