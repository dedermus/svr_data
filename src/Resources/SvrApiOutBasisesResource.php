<?php

namespace Svr\Data\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;


class SvrApiOutBasisesResource extends JsonResource
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
            'out_basis_id'						=> $this->resource['out_basis_id'],
            'out_basis_value'					=> $this->resource['out_basis_value_horriot'],
            'out_basis_name'					=> $this->resource['out_basis_name'],
            'out_basis_status'					=> $this->resource['out_basis_status']
        ];
    }
}
