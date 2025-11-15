<?php

namespace App\Http\Controllers;

use App\Interfaces\DtoInterface;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;
use Symfony\Component\Serializer\SerializerInterface;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function __construct(private readonly SerializerInterface $serializer)
    {
    }

    public function jsonSuccess(array|DtoInterface $data): JsonResponse
    {
        $dataArray = $this->getDataAsArray($data);

        return response()->json([
            'success' => true,
            ...$dataArray,
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function jsonFail(array|DtoInterface $data, int $code = 400): JsonResponse
    {
        $dataArray = $this->getDataAsArray($data);

        return response()->json([
            'success' => false,
            ...$dataArray,
        ], $code, [], JSON_UNESCAPED_UNICODE);
    }

    protected function getDataAsArray(array|DtoInterface $data): array
    {
        if ($data instanceof DtoInterface) {
            return $this->serializer->normalize($data);
        }

        return $data;
    }
}
