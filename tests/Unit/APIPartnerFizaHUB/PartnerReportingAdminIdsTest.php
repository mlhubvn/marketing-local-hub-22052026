<?php

use Modules\APIPartnerFizaHUB\Support\PartnerReportingAdminIds;

test('parses a simple comma-separated list of ids', function (): void {
    expect(PartnerReportingAdminIds::parse('147123468,147123469'))->toBe([147123468, 147123469]);
});

test('trims surrounding whitespace around each id', function (): void {
    expect(PartnerReportingAdminIds::parse(' 147123468 , 147123469 '))->toBe([147123468, 147123469]);
});

test('drops empty segments from repeated or trailing commas', function (): void {
    expect(PartnerReportingAdminIds::parse('147123468,,147123469,'))->toBe([147123468, 147123469]);
});

test('drops non-numeric and negative-looking segments', function (): void {
    expect(PartnerReportingAdminIds::parse('147123468,abc,-5,1.5,147123469'))->toBe([147123468, 147123469]);
});

test('drops a zero id since it is not a valid positive user id', function (): void {
    expect(PartnerReportingAdminIds::parse('0,147123468'))->toBe([147123468]);
});

test('deduplicates repeated ids while preserving first-seen order', function (): void {
    expect(PartnerReportingAdminIds::parse('147123468,147123469,147123468'))->toBe([147123468, 147123469]);
});

test('an empty string yields no allowed ids', function (): void {
    expect(PartnerReportingAdminIds::parse(''))->toBe([]);
});

test('a string of only whitespace and commas yields no allowed ids', function (): void {
    expect(PartnerReportingAdminIds::parse('  ,  , '))->toBe([]);
});

test('a single valid id is parsed correctly', function (): void {
    expect(PartnerReportingAdminIds::parse('147123468'))->toBe([147123468]);
});
