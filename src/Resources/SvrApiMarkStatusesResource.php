<?php

namespace Svr\Data\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;


class SvrApiMarkStatusesResource extends JsonResource
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
            'mark_status_id'					=> $this->resource['mark_status_id'],
            'mark_status_value'					=> $this->resource['mark_status_value_horriot'],
            'mark_status_name'					=> $this->resource['mark_status_name'],
            'mark_status_status'				=> $this->resource['mark_status_status']
        ];
    }
}
