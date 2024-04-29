<?php

namespace App\Http\Resources\User;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * @OA\Schema(
 *     schema="UserCollection",
 *     description="Collection of users",
 *     type="object",
 *     @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/User")),
 *     @OA\Property(property="total", type="integer", example="10"),
 *     @OA\Property(property="per_page", type="integer", example="10"),
 * )
 */

class UserCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
            'total' => $this->total(), //$request->query('pagination') == 'false' ? $this->collection->count() : $this->total(),
            'per_page' => $request->query('pagination') == 'false' ? $this->collection->count() : $this->perPage(),
        ];
    }
}
