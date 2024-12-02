<?php

namespace Svr\Data\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;


class SvrApiAnimalsListMarkResource extends JsonResource
{

    /**
     * Transform the resource collection into an array.
     *
     * @param Request|Collection $request
     * @return array
     */
    public function toArray(Request|Collection $request): array
    {
        $items = [];
        foreach ($this->resource['mark_data'] as $mark) {
            $items[] = new SvrApiAnimalsDataMarkResource(collect($mark));
        }
        return [
            'animal_id' => $this->resource['animal_id'],
            'items' => $items
        ];
    }
}
