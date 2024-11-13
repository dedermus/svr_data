<?php

namespace Svr\Data\Actions;

use OpenAdminCore\Admin\Actions\RowAction;

class CompanyObjectsList extends RowAction
{
    public $name = 'список поднадзорных объектов';

    public $icon = 'icon-copy';

    public function href()
    {
        return "/admin/data/svr_companies_objects?company_id={$this->getKey()}";
    }

}
