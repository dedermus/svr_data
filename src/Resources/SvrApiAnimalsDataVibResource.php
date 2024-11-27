<?php

namespace Svr\Data\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;


class SvrApiAnimalsDataVibResource extends JsonResource
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
            'animal_id' 		=> $this->resource['animal_id'],
            'animal_out_date' 	=> $this->resource['animal_out_date'],
            'animal_out_reason' => $this->resource['animal_out_reason'],
            'animal_out_rashod' => $this->resource['animal_out_rashod'],
            'animal_out_weight' => $this->resource['animal_out_weight'],
            'animal_out_type' 	=> $this->resource['animal_out_type_id'],
            'animal_out_basis' 	=> $this->resource['animal_out_basis_id'],
        ];
    }
}
