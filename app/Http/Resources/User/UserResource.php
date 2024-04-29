<?php

namespace App\Http\Resources\User;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="UserResource",
 *     description="User resource",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example="1"),
 *     @OA\Property(property="first_name", type="string", example="John"),
 *     @OA\Property(property="last_name", type="string", example="Doe"),
 *     @OA\Property(property="email", type="string", format="email", example="johndoe@example.com"),
 *     @OA\Property(property="address", type="string", example="123 Main St"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-04-30T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-04-30T10:00:00Z")
 * )
 */

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */


    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'address' => $this->details->address ?? '',
            'created_at' => Carbon::parse($this->created_at)->format('d-m-Y H:m'),
            'updated_at' => Carbon::parse($this->updated_at)->format('d-m-Y H:m'),
        ];
    }
}
