<?php

namespace Svr\Data\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;


class SvrApiDistrictsResource extends JsonResource
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
            'district_id'					=> $this->resource['district_id'],
            'region_id'					    => $this->resource['region_id'],
            'district_name'					=> $this->resource['district_name'],
            'district_status'				=> $this->resource['district_status']
        ];
    }
}
