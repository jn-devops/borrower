<?php

return [
    'default_regional' => env('DEFAULT_REGIONAL_BORROWER', false),
    'lending_institutions' => [
        'hdmf' => [
            'name' => 'Home Development Mutual Fund',
            'alias' => 'Pag-IBIG',
            'type' => 'government financial institution',
            'borrowing_age' => [
                'minimum' => 18,
                'maximum' => 60,
            ],
        ],
        'rcbc' => [
            'name' => 'Rizal Commercial Banking Corporation',
            'alias' => 'RCBC',
            'type' => 'universal bank',
            'borrowing_age' => [
                'minimum' => 18,
                'maximum' => 60,
            ],
        ],
        'cbc' => [
            'name' => 'China Banking Corporation',
            'alias' => 'CBC',
            'type' => 'universal bank',
            'borrowing_age' => [
                'minimum' => 18,
                'maximum' => 60,
            ],
        ],
    ],
    'default_lending_institution' => env('DEFAULT_LENDING_INSTITUTION', 'hdmf'),
//    'borrowing_age' => [
//        'minimum' => 18,
//        'maximum' => [
//            'hdmf' => 60,
//            'rcbc' => 60,
//            'cbc' => 60,
//            'default' => env('DEFAULT_BORROWING_MAXIMUM_AGE', 60),
//        ]
//    ],
];
