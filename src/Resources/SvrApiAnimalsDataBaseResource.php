<?php

namespace Svr\Data\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;


class SvrApiAnimalsDataBaseResource extends JsonResource
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
            'animal_gender' 		=> $this->resource['animal_gender_id'],
            'animal_sex' 			=> strtolower($this->resource['animal_sex']),
            'animal_date_birth' 	=> $this->resource['animal_date_birth'],
            'animal_breed' 			=> $this->resource['animal_breed_id'],
            'animal_breeding_value' => $this->resource['animal_breeding_value'],
            'animal_mast' 			=> $this->resource['animal_colour'],
            'animal_keeping_company'=> $this->resource['animal_keeping_company_id'],
            'animal_keeping_object' => $this->resource['animal_object_of_keeping_id'],
            'animal_birth_company' 	=> $this->resource['animal_birth_company_id'],
            'animal_birth_object' 	=> $this->resource['animal_object_of_birth_id'],
            'animal_keeping_type'	=> $this->resource['animal_type_of_keeping_id'],
            'animal_keeping_purpose'=> $this->resource['animal_keeping_purpose_id'],
            'animal_date_income' 	=> $this->resource['animal_date_income'],
            'animal_country_import' => $this->resource['animal_country_nameport_id']
        ];
    }
}
