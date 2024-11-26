<?php

namespace Svr\Data\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;


class SvrApiApplicationsListResource extends JsonResource
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
            'user_id' => $this->resource['user']['user_id'],
            'user_first' => $this->resource['user']['user_first'],
            'user_middle' => $this->resource['user']['user_middle'],
            'user_last' => $this->resource['user']['user_last'],
            'user_status' => $this->resource['user']['user_status'],
            'company_name_short' => $this->resource['company_data']['company_name_short'] ?? '',
            'company_name_full' => $this->resource['company_data']['company_name_full'] ?? '',
            'region_name' => $this->resource['region_data']['region_name'] ?? '',
            'district_name' => $this->resource['district_data']['district_name'] ?? '',
            'role_name_long' => $this->resource['role_data']['role_name_long'] ?? '',
            'role_slug' => $this->resource['role_data']['role_slug'] ?? ''
        ];
    }
}
