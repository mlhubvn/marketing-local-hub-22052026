<?php

use Modules\AppBusinessProfiles\Support\BusinessTypeCatalog;
use Modules\AppBusinessProfiles\Livewire\Concerns\ManagesBusinessForm;

function mlhubTaxonomyGroupsByCode(): array
{
    return collect(BusinessTypeCatalog::taxonomyTree())->keyBy('code')->all();
}

function mlhubTaxonomyCategoriesByCode(): array
{
    return collect(BusinessTypeCatalog::taxonomyTree())
        ->flatMap(fn (array $group): array => collect($group['categories'])
            ->mapWithKeys(fn (array $category): array => [
                $category['code'] => $category + ['group_code' => $group['code']],
            ])
            ->all())
        ->all();
}

test('industry taxonomy keeps expected group and priority contracts', function (): void {
    $groups = mlhubTaxonomyGroupsByCode();

    expect($groups)->toHaveCount(18)
        ->and(array_keys($groups))->toBe(BusinessTypeCatalog::groupCodes())
        ->and(BusinessTypeCatalog::priorityGroupCodes())->toHaveCount(9);

    foreach (BusinessTypeCatalog::priorityGroupCodes() as $groupCode) {
        expect($groups)->toHaveKey($groupCode)
            ->and(BusinessTypeCatalog::isValidGroup($groupCode))->toBeTrue()
            ->and($groups[$groupCode]['is_priority'])->toBeTrue();
    }
});

test('industry categories resolve to valid legacy types and compact metadata snapshots', function (): void {
    $groups = mlhubTaxonomyGroupsByCode();
    $categories = mlhubTaxonomyCategoriesByCode();
    $legacyTypes = array_keys(BusinessTypeCatalog::typeOptions());
    $snapshotKeys = [
        'group_code',
        'category_code',
        'legacy_type',
        'recommended_modules',
        'default_campaign_goals',
        'alternative_data_signals',
        'dashboard_preset',
        'template_pack',
        'compliance_sensitive',
        'avoid_medical_claims',
        'taxonomy_version',
        'captured_at',
    ];

    expect($categories)->not->toBeEmpty()
        ->and(array_keys($categories))->toBe(BusinessTypeCatalog::categoryCodes());

    foreach ($categories as $categoryCode => $category) {
        expect($groups)->toHaveKey($category['group_code']);

        $resolved = BusinessTypeCatalog::resolveSelection($category['group_code'], $categoryCode);
        $snapshot = $resolved['metadata_snapshot'];

        expect($resolved['group_code'])->toBe($category['group_code'])
            ->and($resolved['category_code'])->toBe($categoryCode)
            ->and($resolved['legacy_type'])->not->toBe('')
            ->and($legacyTypes)->toContain($resolved['legacy_type'])
            ->and($resolved['taxonomy_version'])->toBe(BusinessTypeCatalog::TAXONOMY_VERSION)
            ->and(array_keys($snapshot))->toBe($snapshotKeys)
            ->and($snapshot)->not->toHaveKey('aliases')
            ->and($snapshot['alternative_data_signals'])
            ->each->toBeIn(BusinessTypeCatalog::SIGNALS);
    }
});

test('legacy catalog API remains available for older callers', function (): void {
    expect(method_exists(BusinessTypeCatalog::class, 'groupedOptions'))->toBeTrue()
        ->and(method_exists(BusinessTypeCatalog::class, 'popularOptions'))->toBeTrue();

    $grouped = BusinessTypeCatalog::groupedOptions();
    $popular = BusinessTypeCatalog::popularOptions();

    expect($grouped)->not->toBeEmpty()
        ->and($popular)->toHaveCount(6);

    foreach ($popular as $option) {
        expect($option)->toHaveKeys(['type', 'label', 'group', 'icon', 'description'])
            ->and(BusinessTypeCatalog::normalizeType($option['type']))->toBe($option['type']);
    }
});

test('legacy other metadata uses taxonomy fallback group', function (): void {
    expect(BusinessTypeCatalog::metadataFor('Other')['group'])->toBe(BusinessTypeCatalog::FALLBACK_GROUP);
});

test('form hydration repairs stale saved group for a valid category', function (): void {
    $form = new class
    {
        use ManagesBusinessForm {
            hydrateIndustrySelection as public;
        }
    };

    $form->hydrateIndustrySelection('Coffee shop', 'retail_goods', 'cafe_milk_tea');

    expect($form->industry_group_code)->toBe('food_beverage')
        ->and($form->industry_category_code)->toBe('cafe_milk_tea');
});

test('legacy type inference and aliases support required create edit flows', function (string $input, string $legacyType, string $groupCode, string $categoryCode): void {
    expect(BusinessTypeCatalog::normalizeType($input))->toBe($legacyType)
        ->and(BusinessTypeCatalog::inferFromLegacyType($legacyType))->toBe([
            'group_code' => $groupCode,
            'category_code' => $categoryCode,
        ]);
})->with([
    'cafe' => ['cafe', 'Coffee shop', 'food_beverage', 'cafe_milk_tea'],
    'ca phe' => ['ca phe', 'Coffee shop', 'food_beverage', 'cafe_milk_tea'],
    'nha khoa' => ['nha khoa', 'Dentist', 'health_dental_fitness', 'dental_clinic'],
    'tap hoa' => ['tap hoa', 'Local store', 'retail_goods', 'grocery_minimart'],
    'ocop' => ['ocop', 'Local store', 'retail_goods', 'souvenir_ocop_gifts'],
    'sua xe' => ['sua xe', 'Auto repair', 'technical_repair_maintenance', 'auto_motorbike_repair'],
    'agency' => ['agency', 'Agency client', 'professional_b2b_services', 'marketing_agency'],
    'ban buon' => ['ban buon', 'Other', 'other_needs_classification', 'other_not_sure'],
    'other' => ['Other', 'Other', 'other_needs_classification', 'other_not_sure'],
]);

test('health and sensitive categories are compliance flagged', function (string $categoryCode): void {
    $category = mlhubTaxonomyCategoriesByCode()[$categoryCode] ?? null;
    $resolved = BusinessTypeCatalog::resolveSelection(null, $categoryCode);

    expect($category)->not->toBeNull()
        ->and($category['compliance_sensitive'])->toBeTrue()
        ->and($resolved['metadata_snapshot']['compliance_sensitive'])->toBeTrue();
})->with([
    'medical clinic' => ['medical_clinic'],
    'dental clinic' => ['dental_clinic'],
    'therapy rehabilitation' => ['therapy_rehabilitation'],
    'pharmacy retail' => ['pharmacy_retail'],
    'pharmacy health retail' => ['pharmacy_health_retail'],
    'nutrition wellness coach' => ['nutrition_wellness_coach'],
    'pharma medical wholesale' => ['pharma_medical_wholesale'],
    'restricted sensitive review' => ['restricted_sensitive_review'],
]);
