<?php

namespace Svr\Data\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;


class SvrApiAnimalsDataGenealogyResource extends JsonResource
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
            'animal_id' => $this->resource['animal_id'],
            'o' =>
                [
                    'father_guid' 			=> $this->resource['animal_father_num'],
                    'father_rshn' 			=> $this->resource['animal_father_rshn'],
                    'father_inv' 			=> $this->resource['animal_father_inv'],
                    'father_date_birth'		=> $this->resource['animal_father_date_birth'],
                    'father_breed'			=> $this->resource['animal_father_breed_id']
                ],
            'm' =>
                [
                    'mother_guid' 			=> $this->resource['animal_mother_num'],
                    'mother_rshn' 			=> $this->resource['animal_mother_rshn'],
                    'mother_inv' 			=> $this->resource['animal_mother_inv'],
                    'mother_date_birth'		=> $this->resource['animal_mother_date_birth'],
                    'mother_breed' 			=> $this->resource['animal_mother_breed_id']
                ],
        ];
    }
}
