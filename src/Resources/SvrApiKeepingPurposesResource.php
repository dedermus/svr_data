<?php

namespace Svr\Data\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;


class SvrApiKeepingPurposesResource extends JsonResource
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
            'keeping_purpose_id'				=> $this->resource['keeping_purpose_id'],
            'keeping_purpose_guid'				=> $this->resource['keeping_purpose_guid_horriot'],
            'keeping_purpose_name'				=> $this->resource['keeping_purpose_name'],
            'keeping_purpose_status'			=> $this->resource['keeping_purpose_status']
        ];
    }
}
