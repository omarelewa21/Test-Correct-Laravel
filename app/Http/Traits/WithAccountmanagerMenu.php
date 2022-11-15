<?php

namespace tcCore\Http\Traits;

trait WithAccountmanagerMenu
{
    protected function menus()
    {
        $menus = [];
        $menus['lists'] = [
            'hasItems' => true,
            'title'    => __('navigation.Database')
        ];
        $menus['files'] = [
            'hasItems' => true,
            'title'    => __('navigation.Bestanden')
        ];
        $menus['qti'] = [
            'hasItems' => true,
            'title'    => __('navigation.QTI')
        ];
        $menus['infos'] = [
            'hasItems' => true,
            'title'    => __('navigation.Info berichten')
        ];
        $menus['imports'] = [
            'hasItems' => true,
            'title'    => __('navigation.Imports')
        ];

        return collect(json_decode(json_encode($menus)));
    }

    protected function tiles()
    {
        $tiles = $this->menus->where('hasItems', true)->mapWithKeys(function ($menuData, $menuName) {
            $getter = $menuName . 'Tiles';
            return [$menuName => self::$getter()];
        });

        return collect(json_decode(json_encode($tiles)));
    }

    protected static function listsTiles()
    {
        $tiles = [];
        $tiles['umbrella_organisations'] = [
            'title'  => __("navigation.Koepelorganisaties"),
            'action' => [
                'method'     => 'cakeRedirect',
                'parameters' => 'database.umbrella_organizations',
            ],
        ];

        $tiles['attainments_import'] = [
            'title'  => __("navigation.Attainments Import"),
            'action' => [
                'method'     => 'cakeRedirect',
                'parameters' => 'database.attainments_import',
            ],
        ];

        $tiles['attainmentscito_import'] = [
            'title'  => __("navigation.Attainments CITO koppeling"),
            'action' => [
                'method'     => 'cakeRedirect',
                'parameters' => 'database.attainmentscito_import',
            ],
        ];

        $tiles['schools'] = [
            'title'  => __("navigation.Scholengemeenschap"),
            'action' => [
                'parameters' => route('account-manager.schools'),
            ],
        ];

        $tiles['school_locations'] = [
            'title'  => __("navigation.Schoollocaties"),
            'action' => [
                'parameters' => route('account-manager.school-locations'),
            ],
        ];

        return $tiles;
    }

    protected static function filesTiles()
    {
        $tiles = [];
        $tiles['class_uploads'] = [
            'title'  => __("navigation.class_files"),
            'action' => [
                'method'     => 'cakeRedirect',
                'parameters' => 'files.class_uploads'
            ],
        ];

        $tiles['test_uploads'] = [
            'title'  => __("navigation.test_files"),
            'action' => [
                'parameters' => route('account-manager.file-management.testuploads')
            ]
        ];

        $tiles['marketing_report'] = [
            'title'  => __("navigation.marketing_report"),
            'action' => [
                'method'     => 'cakeRedirect',
                'parameters' => 'reports.marketing',
            ],
        ];

        $tiles['school_location_report'] = [
            'title'  => __("navigation.school_location_report"),
            'action' => [
                'method'     => 'cakeRedirect',
                'parameters' => 'reports.school_location',
            ],
        ];

        return $tiles;
    }

    protected static function qtiTiles()
    {
        $tiles = [];
        $tiles['qtiimport'] = [
            'title'  => __("navigation.Qti Import"),
            'action' => [
                'method'     => 'cakeRedirect',
                'parameters' => 'qtiimport.index'
            ],
        ];

        $tiles['qtiimport_cito'] = [
            'title'  => __("navigation.Cito"),
            'action' => [
                'method'     => 'cakeRedirect',
                'parameters' => 'qtiimport_cito'
            ],
        ];

        $tiles['qtiimport_batch_cito'] = [
            'title'  => __("navigation.Batch Cito"),
            'action' => [
                'method'     => 'cakeRedirect',
                'parameters' => 'qtiimport_batch_cito'
            ],
        ];
        return $tiles;
    }

    protected static function infosTiles()
    {
        $tiles = [];
        $tiles['info_messages'] = [
            'title'  => __("navigation.Info berichten"),
            'action' => [
                'method'     => 'cakeRedirect',
                'parameters' => 'infos.index',
            ]
        ];
        return $tiles;
    }

    protected static function importsTiles()
    {
        $tiles = [];

        $tiles['uwlr_grid'] = [
            'title'  => __('navigation.uwlr grid'),
            'action' => [
                'parameters' => route('uwlr.grid')
            ]
        ];
        $tiles['uwlr_fetcher'] = [
            'title'  => __('navigation.uwlr fetcher'),
            'action' => [
                'parameters' => route('uwlr.fetcher')
            ]
        ];

        return $tiles;
    }

}