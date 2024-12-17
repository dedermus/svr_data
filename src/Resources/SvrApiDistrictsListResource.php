<?php

namespace Svr\Data\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

class SvrApiDistrictsListResource extends JsonResource
{
    /**
     * Указывает, следует ли сохранить ключи коллекции ресурса.
     *
     * @var bool
     */
    public bool $preserveKeys = true;

    /**
     * Transform the resource collection into an array.
     *
     * @param Request|Collection $request
     * @return array
     */
    public function toArray(Request|Collection $request): array
    {
        $returned_data = [];
        $items_list = $this->resource['districts_list'] ?? $this->resource;
        foreach ($items_list as $value)
        {
            if (empty($this->resource['without_keys']))
            {
                $returned_data[$value->district_id] = new SvrApiDistrictsResource(collect($value));
            } else {
                $returned_data[] = new SvrApiDistrictsResource(collect($value));
            }
        }
        return $returned_data;
    }
}
