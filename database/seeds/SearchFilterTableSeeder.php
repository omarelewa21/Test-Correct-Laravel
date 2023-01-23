<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use tcCore\SearchFilter;
use tcCore\User;

class SearchFilterTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(SearchFilter::class)->create([
            'name' => 'Toetsen 2019',
            'key' => 'item_bank',
            'filters' => (object)[
                'name' => (object)[
                    'name' => 'Toets',
                    'filter' => 'toe',
                    'label' => 'toe',
                ],

                'kind' => (object)[
                    'name' => '',
                    'filter' => '',
                    'label' => '',
                ],

                'subject' => (object)[
                    'name' => 'Vak',
                    'filter' => '6',
                    'label' => 'Demovak',
                ],

                'period' => (object)[
                    'name' => 'Periode',
                    'filter' => '1',
                    'label' => '2018',
                ],

                'educationLevels' => (object)[
                    'name' => '',
                    'filter' => '',
                    'label' => '',
                ],

                'isOpenSourceContent' => (object)[
                    'name' => '',
                    'filter' => '',
                    'label' => '',
                ],

                'createdAtStart' => (object)[
                    'name' => '',
                    'filter' => '',
                    'label' => '',
                ],

                'createdAtEnd' => (object)[
                    'name' => '',
                    'filter' => '',
                    'label' => '',
                ],

            ],
            'user_id' => User::whereUsername('d1@test-correct.nl')->first()->getKey()
        ]);

        factory(SearchFilter::class)->create([
            'name' => 'Toetsen 2020',
            'key' => 'item_bank',
            'filters' => (object)[
                'name' => (object)[
                    'name' => 'toets',
                    'filter' => 'toe',
                    'label' => 'toe',
                ],

                'kind' => (object)[
                    'name' => '',
                    'filter' => '',
                    'label' => '',
                ],

                'subject' => (object)[
                    'name' => 'Vak',
                    'filter' => '1',
                    'label' => 'Nederlands',
                ],

                'period' => (object)[
                    'name' => 'Periode',
                    'filter' => '1',
                    'label' => '2018',
                ],

                'educationLevels' => (object)[
                    'name' => '',
                    'filter' => '',
                    'label' => '',
                ],

                'isOpenSourceContent' => (object)[
                    'name' => '',
                    'filter' => '',
                    'label' => '',
                ],

                'createdAtStart' => (object)[
                    'name' => '',
                    'filter' => '',
                    'label' => '',
                ],

                'createdAtEnd' => (object)[
                    'name' => '',
                    'filter' => '',
                    'label' => '',
                ],

            ],
            'user_id' => User::whereUsername('d1@test-correct.nl')->first()->getKey()
        ]);
    }
}

//     {
//         id: 'abc',
//         name: 'Toetsen 2019',
//         filters: {
//             name: {name: 'Toets', filter: 'toe', label: 'toe'},
//             kind: {name: '', filter: '', label: ''},
//             subject: {name: 'Vak', filter: '1', label: 'Nederlands'},
//             period: {name: 'Niveau', filter: '1', label: 'VWO'},
//             eductionLevels: {name: '', filter: '', label: ''},
//             isOpenSourceContent: {name: '', filter: '', label: ''},
//             createdAtStart: {name: '', filter: '', label: ''},
//             createdAtEnd: {name: '', filter: '', label: ''},
//         }
