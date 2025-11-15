<?php


namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\OpenApi(
    info: new OA\Info(
        version: "1.0.0",
        title: "Slot Service API",
        description: "API для работы со слотами, холдами, подтверждением и т.д."
    ),
)]
class OpenApiSpec
{
}
