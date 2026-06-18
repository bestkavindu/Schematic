<?php

use App\Services\Schema\PostgresIdentifier;

test('quote wraps and escapes embedded double quotes', function () {
    expect(PostgresIdentifier::quote('users'))->toBe('"users"');
    expect(PostgresIdentifier::quote('a"b'))->toBe('"a""b"');
});

test('assertValid accepts safe identifiers', function () {
    PostgresIdentifier::assertValid('valid_name');
    PostgresIdentifier::assertValid('_x1');

    expect(true)->toBeTrue(); // reached only if no exception was thrown
});

test('assertValid rejects unsafe identifiers', function (string $bad) {
    expect(fn () => PostgresIdentifier::assertValid($bad))->toThrow(InvalidArgumentException::class);
})->with(['drop table', 'a-b', '1col', 'a;b', 'a"b', '']);

test('defaultLiteral passes through numbers, keywords and whitelisted functions', function () {
    expect(PostgresIdentifier::defaultLiteral('5'))->toBe('5');
    expect(PostgresIdentifier::defaultLiteral('-3.14'))->toBe('-3.14');
    expect(PostgresIdentifier::defaultLiteral('TRUE'))->toBe('true');
    expect(PostgresIdentifier::defaultLiteral('null'))->toBe('null');
    expect(PostgresIdentifier::defaultLiteral('now()'))->toBe('now()');
    expect(PostgresIdentifier::defaultLiteral('gen_random_uuid()'))->toBe('gen_random_uuid()');
});

test('defaultLiteral escapes everything else as a string literal', function () {
    expect(PostgresIdentifier::defaultLiteral('active'))->toBe("'active'");
    expect(PostgresIdentifier::defaultLiteral("O'Brien"))->toBe("'O''Brien'");
});
