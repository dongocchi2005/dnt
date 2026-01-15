<?php

use App\Services\Chat\ProductPayloadNormalizer;

it('normalizes price from sale_price', function () {
    $products = [
        [
            'id' => 1,
            'name' => 'P1',
            'sale_price' => '320000.00',
            'original_price' => '380000.00',
            'url' => '/p1',
            'image' => null,
            'in_stock' => true,
        ],
    ];

    $out = ProductPayloadNormalizer::normalizeProducts($products);

    expect($out)->toHaveCount(1);
    expect($out[0]['price'])->toBeFloat()->toEqual(320000.0);
});

it('normalizes price from original_price when sale_price is null', function () {
    $products = [
        [
            'id' => 2,
            'name' => 'P2',
            'sale_price' => null,
            'original_price' => '550000.00',
            'url' => '/p2',
            'image' => null,
            'in_stock' => true,
        ],
    ];

    $out = ProductPayloadNormalizer::normalizeProducts($products);

    expect($out)->toHaveCount(1);
    expect($out[0]['price'])->toBeFloat()->toEqual(550000.0);
});

it('normalizes price from original_price when sale_price is 0', function () {
    $products = [
        [
            'id' => 3,
            'name' => 'P3',
            'sale_price' => '0.00',
            'original_price' => '200000.00',
            'url' => '/p3',
            'image' => null,
            'in_stock' => true,
        ],
    ];

    $out = ProductPayloadNormalizer::normalizeProducts($products);

    expect($out)->toHaveCount(1);
    expect($out[0]['price'])->toBeFloat()->toEqual(200000.0);
});

