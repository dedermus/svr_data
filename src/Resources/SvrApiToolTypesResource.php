<?php

namespace Svr\Data\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;


class SvrApiToolTypesResource extends JsonResource
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
            'mark_tool_type_id'					=> $this->resource['mark_tool_type_id'],
            'mark_tool_type_value'				=> $this->resource['mark_tool_type_value_horriot'],
            'mark_tool_type_name'				=> $this->resource['mark_tool_type_name'],
            'mark_tool_type_status'				=> $this->resource['mark_tool_type_status']
        ];
    }
}
