<?php

namespace Svr\Data\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\URL;


class SvrApiAnimalsDataMarkResource extends JsonResource
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
            'mark_id' 			=> $this->resource['code_id'],
            'mark_type' 		=> $this->resource['mark_type_id'],
            'number' 			=> $this->resource['code_value'],
            'mark_status' 		=> $this->resource['mark_status_id'],
            'mark_tool_type' 	=> $this->resource['mark_tool_type_id'],
            'mark_tool_location'=> $this->resource['tool_location_id'],
            'description' 		=> $this->resource['code_description'],
            'photo' 			=> empty($this->resource['code_tool_photo']) ? '' : URL::to('/').'/images/mark_photo/'.$this->resource['code_tool_photo'].'_resized.jpg',
            'mark_date_set'		=> $this->resource['code_tool_date_set'],
            'mark_date_out'		=> $this->resource['code_tool_date_out'],
        ];
    }
}
