<?php

namespace Svr\Data\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;


class SvrApiKeepingTypesResource extends JsonResource
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
            'keeping_type_id'				=> $this->resource['keeping_type_id'],
            'keeping_type_guid'				=> $this->resource['keeping_type_guid_horriot'],
            'keeping_type_name'				=> $this->resource['keeping_type_name'],
            'keeping_type_status'			=> $this->resource['keeping_type_status']
        ];
    }
}
