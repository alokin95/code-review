<?php

namespace App\Query;

use App\Filter\MessageFilter;

final readonly class GetMessagesQuery
{
    public function __construct(public MessageFilter $filter)
    {
    }
}