<?php

namespace Svr\Data\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;


class SvrApiAnimalsDataMainResource extends JsonResource
{

    /**
     * Transform the resource collection into an array.
     *
     * @param Request|Collection $request
     * @return array
     */
    public function toArray(Request|Collection $request): array
    {
        if(empty($this->resource['application_animal_status']))
        {
            $this->resource['application_animal_status']	= 'added';
        }

        $text_error = '';

        if (!empty($this->resource['application_herriot_send_text_error']))
        {
            $text_error = $this->resource['application_herriot_send_text_error'];
        }

        if (!empty($this->resource['application_herriot_check_text_error']))
        {
            $text_error = $this->resource['application_herriot_check_text_error'];
        }

        return [
            'animal_id' 						=> $this->resource['animal_id'],
            'animal_guid' 						=> $this->resource['animal_guid_horriot'],
            'animal_horriot_number'				=> $this->resource['animal_number_horriot'],
            'animal_rshn' 						=> $this->resource['animal_rshn_value'],
            'animal_nanimal' 					=> $this->resource['animal_nanimal'],
            'animal_inv' 						=> $this->resource['animal_inv_value'],
            'animal_specie' 					=> $this->resource['animal_specie_id'],
            'animal_gender' 					=> $this->resource['animal_gender_id'],
            'animal_sex' 						=> strtolower($this->resource['animal_sex']),
            'animal_date_birth'					=> $this->resource['animal_date_birth'],
            'text_error'						=> $text_error,
            'animal_keeping_company'			=> $this->resource['animal_keeping_company_id'],
            'animal_keeping_object' 			=> $this->resource['animal_object_of_keeping_id'],
            'animal_birth_company' 				=> $this->resource['animal_birth_company_id'],
            'animal_birth_object' 				=> $this->resource['animal_object_of_birth_id'],
            'animal_breed' 						=> $this->resource['animal_breed_id'],
            'animal_date_reg_herriot'			=> $this->resource['application_animal_date_horriot'],
            'animal_date_reg' 					=> $this->resource['animal_date_create_record'],
            'animal_status_card' 				=> $this->resource['animal_status'],
            'animal_registration_available' 	=> $this->resource['animal_registration_available'],
            'animal_status_record' 				=> $this->resource['application_animal_status'],
        ];
    }
}
