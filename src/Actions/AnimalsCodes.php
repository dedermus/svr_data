<?php

namespace Svr\Data\Actions;

use Illuminate\Database\Eloquent\Model;
use OpenAdminCore\Admin\Actions\RowAction;

class AnimalsCodes extends RowAction
{
    public $name = 'идентификаторы';

    public $icon = 'icon-copy';

    public function handle(Model $model)
    {
        return $this->response()->success('Success message.')->refresh();
    }
}
