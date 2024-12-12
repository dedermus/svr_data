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
		$returned_data = [];

		if(!is_array($this->resource['applications_list']) || count($this->resource['applications_list']) == 0)
		{
			return $returned_data;
		}

		foreach ($this->resource['applications_list'] as $item)
		{
			$returned_data[] = new SvrApiApplicationDataSimpleResource(['application_data' => $item]);
		}

		return $returned_data;
    }
}
