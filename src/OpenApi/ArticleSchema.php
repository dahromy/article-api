<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

class ArticleSchema
{
    #[OA\Property(type: "string")]
    public string $id;

    #[OA\Property(type: "string")]
    public string $name;

    #[OA\Property(type: "string")]
    public string $description;

    #[OA\Property(type: "number", format: "float")]
    public float $price;

    #[OA\Property(type: "integer")]
    public int $quantity;
}