<?php

namespace Svr\Data\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;


class SvrApiRegionsResource extends JsonResource
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
            'region_id'						=> $this->resource['region_id'],
            'country_id'					=> $this->resource['country_id'],
            'region_name'					=> $this->resource['region_name'],
            'region_status'					=> $this->resource['region_status']
        ];
    }
}
