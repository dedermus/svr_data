<?php

namespace Svr\Data\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Svr\Core\Resources\SvrApiUserDistrictResource;

class SvrApiToolsLocationsListResource extends JsonResource
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
        $items = $this->resource['tools_locations_list'] ?? $this->resource;
        foreach ($items as $value)
        {
            $returned_data[$value->tool_location_id] = new SvrApiToolsLocationsResource(collect($value));
        }
        return $returned_data;
    }
}
