<?php

namespace Svr\Data\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;


class SvrApiMarkTypesResource extends JsonResource
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
            'mark_type_id'					=> $this->resource['mark_type_id'],
            'mark_type_value'				=> $this->resource['mark_type_value_horriot'],
            'mark_type_name'				=> $this->resource['mark_type_name'],
            'mark_type_status'				=> $this->resource['mark_type_status']
        ];
    }
}
