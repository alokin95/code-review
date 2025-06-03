<?php

namespace App\ValueResolver;

use App\Enum\MessageStatusEnum;
use App\Filter\MessageFilter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsTargetedValueResolver;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

#[AsTargetedValueResolver('message_filter')]
class MessageFilterValueResolver implements ValueResolverInterface
{
    /**
     * @return iterable<MessageFilter>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($argument->getType() !== MessageFilter::class) {
            return [];
        }

        $statusParam = $request->query->getString('status');

        $status = MessageStatusEnum::tryFrom($statusParam);

        if ($statusParam && $status === null) {
            throw new BadRequestHttpException(sprintf(
                "'%s' is not a valid value for status. Expected: [%s]",
                $statusParam,
                implode(', ', array_map(fn(MessageStatusEnum $status) => $status->value, MessageStatusEnum::cases()))
            ));
        }

        return [new MessageFilter($status)];
    }
}