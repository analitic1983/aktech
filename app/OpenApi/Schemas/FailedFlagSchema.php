<?php
namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ErrorResponse',
    description: 'Стандартный формат ошибки API'
)]
class FailedFlagSchema
{
    #[OA\Property(
        property: 'success',
        type: 'boolean',
        example: false
    )]
    public bool $success;

    #[OA\Property(
        property: 'message',
        type: 'string',
        example: 'Please try again after 5 seconds.'
    )]
    public string $message;
}
