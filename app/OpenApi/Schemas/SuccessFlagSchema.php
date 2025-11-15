<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'SuccessFlag',
    description: 'Флаг успешности ответа'
)]
class SuccessFlagSchema
{
    #[OA\Property(
        property: 'success',
        description: 'Признак успешного ответа',
        type: 'boolean',
        example: true
    )]
    public bool $success;
}
