<?php

namespace App\Exceptions;

use App\Exceptions\Interfaces\BusinessExceptionInterface;
use App\Models\BaseModel;

class ModelNotFoundBusinessException extends \Exception implements BusinessExceptionInterface
{
    public function __construct(string $modelClass, string $id)
    {
        // короткое имя класса без неймспейса
        $short = substr($modelClass, strrpos($modelClass, '\\') + 1);

        parent::__construct(
            sprintf('Модель "%s" с id "%s" не найдена.', $short, $id)
        );
    }
}
