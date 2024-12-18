<?php

namespace Svr\Data\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Svr\Core\Resources\SvrApiUserDistrictResource;

class SvrApiCompaniesObjectsListResource extends JsonResource
{
    /**
     * Указывает, следует ли сохранить ключи коллекции ресурса.
     *
     * @var bool
     */
    public bool $preserveKeys = true;

    /**
     * Transform the resource collection into an array.
     *
     * @param Request|Collection $request
     * @return array
     */
    public function toArray(Request|Collection $request): array
    {
        $returned_data = [];

		if($this->resource['objects_list'] && count($this->resource['objects_list']) > 0)
		{
			foreach ($this->resource['objects_list'] as $value)
			{
				if(!isset($this->resource['without_keys']) || $this->resource['without_keys'] === false)
				{
					$returned_data[$value->company_object_id]	= new SvrApiCompaniesObjectsResource(collect($value));
				}else{
					$returned_data[]							= new SvrApiCompaniesObjectsResource(collect($value));
				}
			}
		}

        return $returned_data;
    }
}
