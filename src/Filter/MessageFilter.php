<?php

namespace App\Filter;

use App\Enum\MessageStatusEnum;

final readonly class MessageFilter
{
    public function __construct(public ?MessageStatusEnum $status = null)
    {}
}