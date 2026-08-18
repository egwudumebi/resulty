<?php

return [
    'grade_points' => [
        'A' => 5,
        'B' => 4,
        'C' => 3,
        'D' => 2,
        'E' => 1,
    ],

    'class_of_degree' => [
        ['min' => 4.50, 'code' => '11', 'label' => 'First Class Honours'],
        ['min' => 3.50, 'code' => '21', 'label' => 'Second Class Honours (Upper Division)'],
        ['min' => 2.40, 'code' => '22', 'label' => 'Second Class Honours (Lower Division)'],
        ['min' => 1.50, 'code' => '23', 'label' => 'Third Class Honours'],
        ['min' => 0.00, 'code' => '24', 'label' => 'Pass'],
    ],

    'gpa_decimal_places' => 2,
];
