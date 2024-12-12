<?php

namespace Svr\Data\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

use Svr\Core\Resources\SvrApiUsersSimpleListResource;


class SvrApiApplicationsListDictionaryResource extends JsonResource
{

    /**
     * Transform the resource collection into an array.
     *
     * @param Request|Collection $request
     * @return array
     */
    public function toArray(Request|Collection $request): array
    {
        return [
			'application_status' 	=> $this->resource['application_status'] ?? [],
//			'users_list'			=> $this->resource['users_list'] ?? []
			'users_list'			=> new SvrApiUsersSimpleListResource($this->resource['users_list'])
        ];
    }
}
