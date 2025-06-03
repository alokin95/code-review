<?php

namespace App\Enum;

enum MessageStatusEnum: string
{
    case Sent = 'sent';
    case Read = 'read';
}
