<?php

use App\Support\BusinessDirectory\BusinessDirectoryMask;

test('business directory mask hides middle of long values', function (): void {
    expect(BusinessDirectoryMask::mask('0905123456'))->toBe('090***456')
        ->and(BusinessDirectoryMask::mask('lienhe@phuclam.vn'))->toBe('lie***.vn')
        ->and(BusinessDirectoryMask::mask('https://phuclam.vn'))->toBe('htt***.vn');
});

test('business directory mask handles short and empty values', function (): void {
    expect(BusinessDirectoryMask::mask('abc'))->toBe('***')
        ->and(BusinessDirectoryMask::mask('12345'))->toBe('12***5')
        ->and(BusinessDirectoryMask::mask(''))->toBeNull()
        ->and(BusinessDirectoryMask::mask(null))->toBeNull();
});
