<?php

namespace App\Tests\QueryHandler;

use App\Entity\Message;
use App\Enum\MessageStatusEnum;
use App\Filter\MessageFilter;
use App\Query\GetMessagesQuery;
use App\QueryHandler\GetMessagesHandler;
use App\Repository\MessageRepository;
use App\ViewModel\MessageView;
use PHPUnit\Framework\TestCase;

class GetMessagesHandlerTest extends TestCase
{
    public function test_it_returns_message_view_models(): void
    {
        $message = new Message();
        $message->setText('Test Message');
        $message->setStatus(MessageStatusEnum::Sent);

        $mockRepository = $this->createMock(MessageRepository::class);
        $mockRepository
            ->expects($this->once())
            ->method('findByFilter')
            ->with($this->isInstanceOf(MessageFilter::class))
            ->willReturn([$message]);

        $handler = new GetMessagesHandler($mockRepository);

        $query = new GetMessagesQuery(new MessageFilter());
        $result = $handler->handle($query);

        $this->assertCount(1, $result);
        $this->assertInstanceOf(MessageView::class, $result[0]);
        $this->assertSame('Test Message', $result[0]->text);
        $this->assertSame(MessageStatusEnum::Sent->value, $result[0]->status);
    }

    public function test_it_returns_empty_array_when_no_messages(): void
    {
        $mockRepository = $this->createMock(MessageRepository::class);
        $mockRepository
            ->expects($this->once())
            ->method('findByFilter')
            ->willReturn([]);

        $handler = new GetMessagesHandler($mockRepository);
        $query = new GetMessagesQuery(new MessageFilter());

        $result = $handler->handle($query);

        $this->assertSame([], $result);
    }
}