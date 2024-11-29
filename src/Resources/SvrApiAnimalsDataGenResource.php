<?php

namespace Svr\Data\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;


class SvrApiAnimalsDataGenResource extends JsonResource
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
            'animal_id' 			=> $this->resource['animal_id'],
            'animal_specie' 		=> $this->resource['animal_specie_id'],
            'animal_rshn' 			=> $this->resource['animal_rshn_value'],
            'animal_inv' 			=> $this->resource['animal_inv_value'],
            'animal_guid' 			=> $this->resource['animal_guid_horriot'],
            'animal_status_card' 	=> $this->resource['animal_status'],
        ];
    }
}
