<?php

namespace Svr\Data\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;


class SvrApiCountriesResource extends JsonResource
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
            'country_id'						=> $this->resource['country_id'],
            'country_guid'						=> $this->resource['country_guid_horriot'],
            'country_name'						=> $this->resource['country_name'],
            'country_status'					=> $this->resource['country_status']
        ];
    }
}
