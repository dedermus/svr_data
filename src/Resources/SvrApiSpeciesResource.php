<?php

namespace Svr\Data\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;


class SvrApiSpeciesResource extends JsonResource
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
            'specie_id'							=> $this->resource['specie_id'],
            'specie_guid'						=> $this->resource['specie_guid_horriot'],
            'specie_name'						=> $this->resource['specie_name'],
            'specie_status'						=> $this->resource['specie_status']
        ];
    }
}
