<?php

use Database\Support\IdSequence;

test('id sequence helpers use configured starting id', function () {
    expect(IdSequence::at(0))->toBe(IdSequence::startingId());
    expect(IdSequence::at(3))->toBe(IdSequence::startingId() + 3);
    expect(IdSequence::fromLegacy(1))->toBe(IdSequence::startingId());
    expect(IdSequence::fromLegacy(5))->toBe(IdSequence::startingId() + 4);
});

test('core tables targeted for legacy id resequence include options and migrations', function () {
    expect(IdSequence::RESEQUENCE_TABLES)->toContain('options', 'migrations');
});
