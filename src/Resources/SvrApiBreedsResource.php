<?php

namespace Svr\Data\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;


class SvrApiBreedsResource extends JsonResource
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
            'breed_id'							=> $this->resource['breed_id'],
            'specie_id'							=> $this->resource['specie_id'],
            'breed_guid'						=> $this->resource['breed_guid_horriot'],
            'breed_name'						=> $this->resource['breed_name'],
            'breed_status'						=> $this->resource['breed_status']
        ];
    }
}
