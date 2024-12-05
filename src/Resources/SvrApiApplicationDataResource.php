<?php

namespace Svr\Data\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

use Svr\Core\Models\SystemUsers;

use Svr\Data\Resources\SvrApiAnimalsListResource;


class SvrApiApplicationDataResource extends JsonResource
{

    /**
     * Transform the resource collection into an array.
     *
     * @param Request|Collection $request
     * @return array
     */
    public function toArray(Request|Collection $request): array
    {
		$user_avatars			= (new SystemUsers())->getCurrentUserAvatar(4);
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
        	"user_id"						=> $application_data->user_id,
        	"user_last"						=> $application_data->user_last,
        	"user_first"					=> $application_data->user_first,
        	"user_middle"					=> $application_data->user_middle,
        	"user_avatar_small"				=> $user_avatars['user_avatar_small'],
        	"user_avatar_big"				=> $user_avatars['user_avatar_big'],
        	"user_status"					=> $application_data->user_status,
        	"user_date_created"				=> $application_data->user_date_created,
        	"user_date_block"				=> $application_data->user_date_block,
        	"user_phone"					=> $application_data->user_phone,
        	"user_email"					=> $application_data->user_email,
        	"user_herriot_data"				=> [
				"login"							=> "***",
        	    "password"						=> "***"
        	],
        	"animals_list"					=> new SvrApiAnimalsListResource(collect([
				'animals_list' 					=> $this->resource['animals_list'],
				'data_sections'					=> ['main']
			])),
        ];
    }
}
