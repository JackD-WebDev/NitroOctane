<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class UserCollection extends ResourceCollection
{
    /**
    * The resource class used for each item in the collection.
     * @var string
     */
    public $collects = UserResource::class;

    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
            'links' => [
                'self' => url('/user'),
                'client' => url(config('app.frontend_url').'/user')
            ],
            'meta' => [
                'documentation_url' => url('/docs/user')
            ]
        ];
    }
}
