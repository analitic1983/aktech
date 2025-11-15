<?php

namespace App\Exceptions;

use App\Exceptions\Interfaces\BusinessExceptionInterface;

class HoldMismatchException  extends \Exception implements BusinessExceptionInterface
{
}
