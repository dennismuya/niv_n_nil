<?php

namespace App\Http\Resources;


use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [

            "status"=>true,
            "message"=>$this->message_,
            "profile"=>[
                "user_id"=>$this->id,
                "user_name"=>$this->user_name,
            ],
            "auth_token"=>$this->token,
        ];
    }
}
