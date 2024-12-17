<?php

namespace Svr\Data\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Svr\Core\Resources\SvrApiUserDistrictResource;

class SvrApiBreedsListResource extends JsonResource
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
        $items_list = $this->resource['breeds_list'] ?? $this->resource;
        foreach ($items_list as $value)
        {
            if (empty($this->resource['without_keys']))
            {
                $returned_data[$value->breed_id] = new SvrApiBreedsResource(collect($value));
            } else {
                $returned_data[] = new SvrApiBreedsResource(collect($value));
            }

        }
        return $returned_data;
    }
}
