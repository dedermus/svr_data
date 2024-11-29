<?php

namespace Svr\Data\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;


class SvrApiAnimalsListResource extends JsonResource
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
        foreach ($this->resource['animals_list'] as $animal)
        {
            $animal_widget = [];
            foreach ($this->resource['data_sections'] as $section) {
                if ($section == 'mark') {
                    $resource_name = SvrApiAnimalsListMarkResource::class;
                    $animal_widget[$section] = new $resource_name(['animal_id' => $animal['animal_id'], 'mark_data' => $animal['mark_data']]);
                    continue;
                }
                $resource_name = 'Svr\Data\Resources\SvrApiAnimalsData'.ucfirst($section).'Resource';
                $animal_widget[$section] = new $resource_name($animal);
            }
            $returned_data[] = $animal_widget;
        }

        return $returned_data;
    }
}
