<?php

use Homeful\Borrower\Classes\LendingInstitution;

dataset('lending institutions', function () {
    return [
        fn() => ['key' => 'hdmf', 'max_age' => 60, 'name' => 'Home Development Mutual Fund'],
        fn() => ['key' => 'rcbc', 'max_age' => 60, 'name' => 'Rizal Commercial Banking Corporation'],
        fn() => ['key' => 'cbc',  'max_age' => 60, 'name' => 'China Banking Corporation'],
    ];
});

it('has a default', function () {
    $inst = new LendingInstitution;
    expect($inst->getKey())->toBe('hdmf');
});

it('has records', function (array $params) {
    $key = $params['key'];
    $inst = new LendingInstitution($key);
    expect($inst->getName())->toBe($params['name']);
    expect($inst->getMaximumBorrowingAge())->toBe($params['max_age']);
})->with('lending institutions');
