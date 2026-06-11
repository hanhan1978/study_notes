<?php

declare(strict_types=1);

require_once __DIR__ . '/turing_machine.php';

function assertSameValue(mixed $expected, mixed $actual, string $message): void
{
    if ($expected !== $actual) {
        fwrite(STDERR, sprintf("%s\nexpected: %s\nactual:   %s\n", $message, var_export($expected, true), var_export($actual, true)));
        exit(1);
    }
}

function assertThrows(callable $callback, string $expectedClass, string $message): void
{
    try {
        $callback();
    } catch (Throwable $e) {
        if ($e instanceof $expectedClass) {
            return;
        }

        fwrite(STDERR, sprintf("%s\nexpected: %s\nactual:   %s: %s\n", $message, $expectedClass, get_class($e), $e->getMessage()));
        exit(1);
    }

    fwrite(STDERR, $message . "\nexpected exception: " . $expectedClass . "\nactual: none\n");
    exit(1);
}

$machine = buildUnaryAdditionMachine();

assertSameValue('11111', $machine->run('111+11', false), '111+11 should become 11111');
assertSameValue('11', $machine->run('1+1', false), '1+1 should become 11');
assertSameValue('111', $machine->run('+111', false), '+111 should become 111');
assertSameValue('111111', $machine->run('111+111', false), '111+111 should become 111111');

assertThrows(
    fn () => $machine->run('abc', false),
    InvalidArgumentException::class,
    'abc should be rejected'
);

assertThrows(
    fn () => $machine->run('111++1', false),
    InvalidArgumentException::class,
    'multiple plus signs should be rejected'
);

assertSameValue(
    '結果: 11111 (3 + 2 = 5)',
    formatUnaryAdditionSummary('111+11', '11111'),
    'summary formatter should show the operand sizes'
);

echo "All tests passed.\n";
