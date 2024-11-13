<?php

namespace Svr\Data\Actions;

use OpenAdminCore\Admin\Actions\RowAction;

class CompanyData extends RowAction
{
    public $name = 'перейти к организации';

    public $icon = 'icon-copy';

    public function href()
    {
        return "/admin/data/svr_companies/{$this->row('company_id')}";
    }
}
