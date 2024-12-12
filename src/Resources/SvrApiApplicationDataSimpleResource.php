<?php

namespace Svr\Data\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

use Svr\Core\Models\SystemUsers;

use Svr\Data\Resources\SvrApiAnimalsListResource;


class SvrApiApplicationDataSimpleResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request|Collection $request
     * @return array
     */
    public function toArray(Request|Collection $request): array
    {
		$application_data		= $this->resource['application_data'];

        return [
			"application_id"				=> $application_data->application_id,
        	"company_name_short"			=> $application_data->company_name_short,
        	"region_name"					=> $application_data->region_name,
        	"district_name"					=> $application_data->district_name,
        	"application_date_create"		=> $application_data->application_date_create,
        	"application_date_horriot"		=> $application_data->application_date_horriot,
        	"application_date_complete"		=> $application_data->application_date_complete,
        	"application_status"			=> $application_data->application_status,
        	"user_id"						=> $application_data->user_id
        ];
    }
}
