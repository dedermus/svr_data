<?php

namespace Svr\Data\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;


class SvrApiOutTypesResource extends JsonResource
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
            'out_type_id'						=> $this->resource['out_type_id'],
            'out_type_value'					=> $this->resource['out_type_value_horriot'],
            'out_type_name'					    => $this->resource['out_type_name'],
            'out_type_status'					=> $this->resource['out_type_status']
        ];
    }
}
