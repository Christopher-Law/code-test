<?php

return [
    'tax_rate' => (float) env('CART_TAX_RATE', 0.0835),

    'optimization' => [
        'weights' => [
            'price' => (float) env('OPTIMIZATION_WEIGHT_PRICE', 0.40),
            'shipping' => (float) env('OPTIMIZATION_WEIGHT_SHIPPING', 0.20),
            'delivery_speed' => (float) env('OPTIMIZATION_WEIGHT_DELIVERY_SPEED', 0.25),
            'availability' => (float) env('OPTIMIZATION_WEIGHT_AVAILABILITY', 0.15),
        ],
    ],
];
