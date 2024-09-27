<?php

use Homeful\Borrower\Classes\LendingInstitution;

dataset('lending institutions', function () {
    return [
        fn() => ['key' => 'hdmf', 'max_age' => 60, 'name' => 'Home Development Mutual Fund', 'maximum_term' => 30],
        fn() => ['key' => 'rcbc', 'max_age' => 60, 'name' => 'Rizal Commercial Banking Corporation', 'maximum_term' => 20],
        fn() => ['key' => 'cbc',  'max_age' => 60, 'name' => 'China Banking Corporation', 'maximum_term' => 20],
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
    expect($inst->getMaximumTerm())->toBe($params['maximum_term']);
})->with('lending institutions');
