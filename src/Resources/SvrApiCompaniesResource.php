<?php

namespace Svr\Data\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;


class SvrApiCompaniesResource extends JsonResource
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
            'company_id'         		=> $this->resource['company_id'],
            'company_name_full'  		=> $this->resource['company_name_full'],
            'company_name_short' 		=> $this->resource['company_name_short'],
            'company_status'     		=> $this->resource['company_status'],
            'company_locations_list'	=> [],
        ];
    }
}
