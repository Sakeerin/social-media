<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FollowResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'created_at' => $this->created_at,

            // Include follower (from_id) when loaded
            'follower' => new UserMetaResource($this->whenLoaded('follower')),

            // Include following (to_id) when loaded
            'following' => new UserMetaResource($this->whenLoaded('following')),
        ];
    }
}
