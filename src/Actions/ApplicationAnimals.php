<?php

namespace Svr\Data\Actions;

use OpenAdminCore\Admin\Actions\RowAction;

class ApplicationAnimals extends RowAction
{
    public $name = 'Животные';

    public $icon = 'icon-bug';

	public function href()
	{
		return "/admin/data/svr_animals?application_id={$this->getKey()}";
	}
}
