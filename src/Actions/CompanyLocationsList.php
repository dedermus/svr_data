<?php

namespace Svr\Data\Actions;

use OpenAdminCore\Admin\Actions\RowAction;

class CompanyLocationsList extends RowAction
{
    public $name = 'список локаций компании';

    public $icon = 'icon-copy';

    public function href()
    {
        return "/admin/data/svr_companies_locations?company_id={$this->getKey()}";
    }

}
