<?php

namespace App\Exceptions;

use App\Exceptions\Interfaces\BusinessExceptionInterface;

class SlotUnavailableException extends \Exception implements BusinessExceptionInterface
{
}
