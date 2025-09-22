<?php

namespace App\Http\Resources;

use Agent;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Indicates if the resource should be wrapped.
     * Setting it to null means no wrapping.
     * @var null
     */
    public static $wrap = null;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => [
                'type' => 'user',
                'user_id' => $this->id,
                $this->mergeWhen(auth()->id() == $this->id, [
                    'has2FA' => (bool)$this->two_factor_secret,
                ]),
                'attributes' => [
                    'name' => $this->name,
                    'username' => $this->username,
                    'email_verified_at' => $this->email_verified_at,
                    $this->mergeWhen(auth()->check() && auth()->id() == $this->id, [
                        'email' => $this->email
                    ]),
                    'preferred_language' => $this->lang,
                    'created_at_dates' => [
                        'created_at_human' => $this->created_at->diffForHumans(),
                        'created_at' => $this->created_at
                    ],
                    'updated_at_dates' => [
                        'updated_at_human' => $this->updated_at->diffForHumans(),
                        'updated_at' => $this->updated_at
                    ]
                ]
            ],
            'links' => [
                'self' => url('/users/' . $this->id),
                'client' => url(config('app.frontend_url') . '/users/' . $this->id)
            ],
            'meta' => [
                'documentation_url' => url('/docs/user'),
                'request_agent' => $request->userAgent()
            ]
        ];
    }
}
