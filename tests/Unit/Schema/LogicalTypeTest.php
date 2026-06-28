<?php

use App\Services\Schema\LogicalType;

test('every legacy type name maps back to itself', function (string $legacy) {
    expect(LogicalType::toLaravel(['type' => $legacy]))->toBe($legacy);
})->with([
    'id', 'bigInteger', 'unsignedBigInteger', 'integer', 'string', 'text',
    'boolean', 'date', 'datetime', 'timestamp', 'json', 'decimal', 'float', 'uuid',
]);

test('logical types collapse to the nearest legacy Laravel type', function (array $column, string $expected) {
    expect(LogicalType::toLaravel($column))->toBe($expected);
})->with([
    'int64 auto-increment is an id' => [['type' => 'int64', 'autoInc' => true, 'unsigned' => true], 'id'],
    'int64 unsigned' => [['type' => 'int64', 'unsigned' => true], 'unsignedBigInteger'],
    'int64 plain' => [['type' => 'int64'], 'bigInteger'],
    'int32' => [['type' => 'int32'], 'integer'],
    'int16' => [['type' => 'int16'], 'integer'],
    'varchar' => [['type' => 'varchar', 'size' => 100], 'string'],
    'char' => [['type' => 'char'], 'string'],
    'text' => [['type' => 'text'], 'text'],
    'binary' => [['type' => 'binary'], 'text'],
    'bool' => [['type' => 'bool'], 'boolean'],
    'time falls back to datetime' => [['type' => 'time'], 'datetime'],
    'timestamptz' => [['type' => 'timestamptz'], 'timestamp'],
    'decimal' => [['type' => 'decimal', 'precision' => 10, 'scale' => 4], 'decimal'],
    'float64' => [['type' => 'float64'], 'float'],
    'uuid' => [['type' => 'uuid'], 'uuid'],
    'enum falls back to string' => [['type' => 'enum'], 'string'],
    'unknown falls back to string' => [['type' => 'mystery'], 'string'],
]);

test('the database auto_increment key is honored as well as autoInc', function () {
    expect(LogicalType::toLaravel(['type' => 'int64', 'auto_increment' => true]))->toBe('id');
});
