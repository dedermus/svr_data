<?php

namespace Svr\Data\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;


class SvrApiAnimalsDataResource extends JsonResource
{

    /**
     * Transform the resource collection into an array.
     *
     * @param Request|Collection $request
     * @return array
     */
    public function toArray(Request|Collection $request): array
    {
        $returned_data = [];
        foreach ($this->resource['data_sections'] as $section) {
            if ($section == 'mark') {
                $resource_name = SvrApiAnimalsListMarkResource::class;
                $returned_data[$section] = new $resource_name(['animal_id' => $this->resource['animal_data']['animal_id'], 'mark_data' => $this->resource['mark_data']]);
                continue;
            }
            $resource_name = 'Svr\Data\Resources\SvrApiAnimalsData'.ucfirst($section).'Resource';
            $returned_data[$section] = new $resource_name($this->resource['animal_data']);
        }
        return $returned_data;
    }
}
