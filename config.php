<?php

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
