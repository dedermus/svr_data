<?php

namespace Svr\Data\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;


class SvrApiToolsLocationsResource extends JsonResource
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
            'tool_location_id'					=> $this->resource['tool_location_id'],
            'tool_location_guid'				=> $this->resource['tool_location_guid_horriot'],
            'tool_location_name'				=> $this->resource['tool_location_name'],
            'tool_location_status'				=> $this->resource['tool_location_status']
        ];
    }
}
