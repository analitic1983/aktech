<?php

namespace App\Exceptions;

use App\Exceptions\Interfaces\BusinessExceptionInterface;

class HoldNotConfirmableException extends \Exception implements BusinessExceptionInterface
{
}
