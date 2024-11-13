<?php


namespace Svr\Data;


use OpenAdminCore\Admin\Admin;
use OpenAdminCore\Admin\Auth\Database\Permission;
use OpenAdminCore\Admin\Auth\Database\Menu;
use OpenAdminCore\Admin\Extension;

use Svr\Data\Controllers\AnimalsController;
use Svr\Data\Controllers\ApplicationsController;
use Svr\Data\Controllers\CompaniesController;
use Svr\Data\Controllers\CompaniesLocationsController;
use Svr\Data\Controllers\CompaniesObjectsController;

class DataManager extends Extension
{

    /**
     * Bootstrap this package.
     *
     * @return void
     */
    public static function boot()
    {
        static::registerRoutes();

        Admin::extend('svr-data', __CLASS__);
    }


    /**
     * Register routes for open-admin.
     *
     * @return void
     */
    public static function registerRoutes()
    {
        parent::routes(function ($router) {
            /* @var \Illuminate\Routing\Router $router */

            $router->resource('data/svr_animals', AnimalsController::class);
            $router->resource('data/svr_applications', ApplicationsController::class);
            $router->resource('data/svr_companies', CompaniesController::class);
            $router->resource('data/svr_companies_objects', CompaniesObjectsController::class);
            $router->resource('data/svr_companies_locations', CompaniesLocationsController::class);
        });
    }


    /**
     * {@inheritdoc}
     */
    public static function import()
    {
        $lastOrder = Menu::max('order');

        $root = [
            'parent_id' => 0,
            'order' => $lastOrder++,
            'title' => 'СВР - Основные данные',
            'icon' => 'icon-anchor',
            'uri' => 'data',
        ];
//        Если нет пункта в меню, добавляем его
        if (!Menu::where('uri', 'data')->exists()) {
            $root = Menu::create($root);

            $menus = [
                [
                    'title' => 'Животные',
                    'icon' => 'icon-pastafarianism',
                    'uri' => 'data/svr_animals',
                ],
                [
                    'title' => 'Заявки',
                    'icon' => 'icon-address-card',
                    'uri' => 'data/svr_applications',
                ],
                [
                    'title' => 'Хозяйства',
                    'icon' => 'icon-campground',
                    'uri' => 'data/svr_companies',
                ],
                [
                    'title' => 'Локации компаний',
                    'icon' => 'icon-map-marker-alt',
                    'uri' => 'data/svr_companies_locations',
                ],
                [
                    'title' => 'Поднадзорные объекты',
                    'icon' => 'icon-camera-retro',
                    'uri' => 'data/svr_companies_objects',
                ]
            ];

            foreach ($menus as $menu) {
                $menu['parent_id'] = $root->id;
                $menu['order'] = $lastOrder++;

                Menu::create($menu);
            }
        }
//      Установка разрешения на роуты по слагу если его нет
        if (!Permission::where('slug', 'svr.data')->exists()) {
            parent::createPermission('Exceptions SVR-DATA', 'svr.data', 'data*');
        }
    }
}
