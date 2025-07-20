<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

class ArticleSchema
{
    #[OA\Property(type: "string")]
    public string $id;

    #[OA\Property(type: "string")]
    public string $title;

    #[OA\Property(type: "string")]
    public string $content;

    #[OA\Property(type: "string")]
    public string $authorId;

    #[OA\Property(type: "array", items: new OA\Items(type: "string"))]
    public array $tags;

    #[OA\Property(type: "string", format: "date-time")]
    public string $createdAt;

    #[OA\Property(type: "string", format: "date-time")]
    public string $updatedAt;
}