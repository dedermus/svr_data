<?php

namespace Svr\Data\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;


class SvrApiAnimalsDataRegistrationResource extends JsonResource
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
            'animal_id' 		=> $this->resource['animal_id'],
            'application_id'	=> $this->resource['application_id'],
            'status'			=> $this->resource['application_animal_status'],
            'text_error'		=> $text_error,
            'date_created' 		=> $this->resource['application_animal_date_add'],
            'date_send' 		=> $this->resource['application_animal_date_horriot'],
            'date_verification' => $this->resource['application_animal_date_horriot'],
            'date_registration' => $this->resource['application_animal_status'] == 'registered' ? $this->resource['application_animal_date_response'] : '',
            'date_response'		=> $this->resource['application_animal_date_response']
        ];
    }
}
