<?php

namespace App\Modules\Slots\Enums;
enum IdempotencyOperationEnum: string
{
    case HOLD = 'hold';
    case CONFIRM = 'confirm';
    case CANCEL = 'cancel';
}
