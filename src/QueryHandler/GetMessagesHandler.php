<?php

namespace App\QueryHandler;

use App\Entity\Message;
use App\Filter\MessageFilter;
use App\Query\GetMessagesQuery;
use App\Repository\MessageRepository;
use App\ViewModel\MessageView;

final readonly class GetMessagesHandler
{
    public function __construct(private MessageRepository $repository)
    {
    }

    public function handle(GetMessagesQuery $query): array
    {
        $messages = $this->repository->findByFilter(
            new MessageFilter($query->filter->status)
        );

        return array_map(
            fn(Message $message) => MessageView::fromEntity($message),
            $messages
        );
    }
}