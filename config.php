<?php
// error_reporting(E_ALL);
// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
return [
    'boxes' => [
        '10' => [
            'size' => 'small',
            'max_point_value' => 450,
            'cost' => 30,
            'currency' => 'USD'
        ],
        '20' => [
            'size' => 'large',
            'max_point_value' => 850,
            'cost' => 50,
            'currency' => 'USD'
        ]
    ],
    'rules' => [
        'currency' => 'usd',
        'min_spending' => 200,
        'min_box_fill_rate' => 80
    ]
];
