<?php

namespace App\Http\Resources\User;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class UserCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        //dd($request->query('pagination'));
        return [
            'data' => $this->collection,
            'total' => $this->total(), //$request->query('pagination') == 'false' ? $this->collection->count() : $this->total(),
            'per_page' => $request->query('pagination') == 'false' ? $this->collection->count() : $this->perPage(),
        ];
    }
}
