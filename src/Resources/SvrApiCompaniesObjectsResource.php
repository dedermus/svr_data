<?php

namespace Svr\Data\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;


class SvrApiCompaniesObjectsResource extends JsonResource
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
            'company_object_id'         	=> $this->resource['company_object_id'],
            'company_id'         			=> $this->resource['company_id'],
            'company_object_guid_herriot'	=> $this->resource['company_object_guid_horriot'],
            'company_object_approval_number'=> $this->resource['company_object_approval_number'],
            'company_object_address_view'   => $this->resource['company_object_address_view'],
            'company_object_is_favorite'   	=> $this->resource['company_object_is_favorite'],
        ];
    }
}
