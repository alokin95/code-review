<?php

declare(strict_types=1);

namespace App\Tests\ValueResolver;

use App\Enum\MessageStatusEnum;
use App\Filter\MessageFilter;
use App\ValueResolver\MessageFilterValueResolver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class MessageFilterValueResolverTest extends TestCase
{
    public function test_it_resolves_valid_enum(): void
    {
        $request = new Request(['status' => MessageStatusEnum::Sent->value]);

        $resolver = new MessageFilterValueResolver();
        $argument = new ArgumentMetadata(
            'filter',
            MessageFilter::class,
            false,
            false,
            null,
            false,
            []
        );

        $result = iterator_to_array($resolver->resolve($request, $argument));

        $this->assertCount(1, $result);
        $this->assertInstanceOf(MessageFilter::class, $result[0]);
        $this->assertSame(MessageStatusEnum::Sent, $result[0]->status);
    }

    public function test_it_resolves_null_when_status_missing(): void
    {
        $request = new Request();

        $resolver = new MessageFilterValueResolver();
        $argument = new ArgumentMetadata(
            'filter',
            MessageFilter::class,
            false,
            false,
            null,
            false
        );


        $result = iterator_to_array($resolver->resolve($request, $argument));

        $this->assertCount(1, $result);
        $this->assertInstanceOf(MessageFilter::class, $result[0]);
        $this->assertNull($result[0]->status);
    }

    public function test_it_throws_exception_on_invalid_enum(): void
    {
        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage("'INVALID' is not a valid value for status");

        $request = new Request(['status' => 'INVALID']);
        $resolver = new MessageFilterValueResolver();
        $argument = new ArgumentMetadata(
            'filter',
            MessageFilter::class,
            false,
            false,
            null,
            false
        );

        iterator_to_array($resolver->resolve($request, $argument));
    }

    public function test_it_ignores_if_argument_type_is_not_message_filter(): void
    {
        $request = new Request(['status' => MessageStatusEnum::Sent->value]);
        $resolver = new MessageFilterValueResolver();
        $argument = new ArgumentMetadata(
            'filter',
            null,
            false,
            false,
            null,
            false
        );

        $result = iterator_to_array($resolver->resolve($request, $argument));

        $this->assertSame([], $result);
    }
}
