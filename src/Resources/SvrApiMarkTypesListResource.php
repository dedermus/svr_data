<?php

namespace Svr\Data\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Svr\Core\Resources\SvrApiUserDistrictResource;

class SvrApiMarkTypesListResource extends JsonResource
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
        $items = $this->resource['mark_types_list'] ?? $this->resource;
        foreach ($items as $value)
        {
            $returned_data[$value->mark_type_id] = new SvrApiMarkTypesResource(collect($value));
        }
        return $returned_data;
    }
}
