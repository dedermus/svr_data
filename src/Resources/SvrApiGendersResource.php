<?php

namespace Svr\Data\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;


class SvrApiGendersResource extends JsonResource
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
            'gender_id'						=> strtolower($this->resource['gender_value_horriot']),
            'gender_value'					=> $this->resource['gender_value_horriot'],
            'gender_name'					=> $this->resource['gender_name'],
            'gender_status'					=> $this->resource['gender_status']
        ];
    }
}
