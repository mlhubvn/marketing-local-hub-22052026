<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\AdminSettings\Support\OptionStore;
use Modules\CustomMLHUB\Actions\SeedStaticPagesAction;
use Modules\CustomMLHUB\Console\Commands\MLHUBInstallCommand;

function createMLHUBStaticPagesOptionsTable(): void
{
    Schema::dropIfExists('options');

    Schema::create('options', function (Blueprint $table): void {
        $table->id();
        $table->string('name', 255);
        $table->longText('value')->nullable();
        $table->timestamps();
        $table->unique('name', 'options_name_unique');
    });
}

function mlhubStaticPagesDefaults(): array
{
    return (array) require base_path('modules/CustomMLHUB/Database/data/mlhub_static_pages.php');
}

function seedStaticPagesAction(): SeedStaticPagesAction
{
    return new SeedStaticPagesAction(app(OptionStore::class));
}

beforeEach(function (): void {
    createMLHUBStaticPagesOptionsTable();
    Cache::flush();
});

afterEach(function (): void {
    Schema::dropIfExists('options');
    Cache::flush();
});

test('seeds missing static page keys', function (): void {
    $result = seedStaticPagesAction()->handle();
    $defaults = mlhubStaticPagesDefaults();
    $options = app(OptionStore::class);

    expect($result['updated'])->toEqual(SeedStaticPagesAction::OPTION_KEYS)
        ->and($result['preserved'])->toBe([])
        ->and($options->get('privacy_policy_title'))->toBe($defaults['privacy_policy_title'])
        ->and($options->get('privacy_policy_content'))->toBe($defaults['privacy_policy_content'])
        ->and($options->get('terms_of_use_title'))->toBe($defaults['terms_of_use_title'])
        ->and($options->get('terms_of_use_content'))->toBe($defaults['terms_of_use_content']);
});

test('seeds stored null static page values', function (): void {
    foreach (SeedStaticPagesAction::OPTION_KEYS as $key) {
        DB::table('options')->insert(['name' => $key, 'value' => null]);
    }

    $result = seedStaticPagesAction()->handle();

    expect($result['updated'])->toEqual(SeedStaticPagesAction::OPTION_KEYS)
        ->and($result['preserved'])->toBe([]);
});

test('seeds empty static page values', function (): void {
    $options = app(OptionStore::class);

    foreach (SeedStaticPagesAction::OPTION_KEYS as $key) {
        $options->set($key, '   ');
    }

    $result = seedStaticPagesAction()->handle();

    expect($result['updated'])->toEqual(SeedStaticPagesAction::OPTION_KEYS)
        ->and($result['preserved'])->toBe([]);
});

test('seeds html entity whitespace placeholder values', function (): void {
    $options = app(OptionStore::class);
    $placeholders = [
        'privacy_policy_title' => '<p>Nội dung đang cập nhật...</p>',
        'privacy_policy_content' => "  \nNội dung đang cập nhật...\n  ",
        'terms_of_use_title' => '&#32;Nội dung đang cập nhật...',
        'terms_of_use_content' => '<div><span>Nội dung đang cập nhật...</span></div>',
    ];

    foreach ($placeholders as $key => $value) {
        $options->set($key, $value);
    }

    $result = seedStaticPagesAction()->handle();
    $defaults = mlhubStaticPagesDefaults();

    expect($result['updated'])->toEqual(SeedStaticPagesAction::OPTION_KEYS)
        ->and($options->get('privacy_policy_content'))->toBe($defaults['privacy_policy_content']);
});

test('preserves custom values without force', function (): void {
    $options = app(OptionStore::class);
    $options->set('privacy_policy_title', 'Custom Privacy Title');
    $options->set('privacy_policy_content', '<p>Custom privacy body</p>');
    $options->set('terms_of_use_title', 'Custom Terms Title');
    $options->set('terms_of_use_content', '<p>Custom terms body</p>');

    $result = seedStaticPagesAction()->handle(force: false);

    expect($result['updated'])->toBe([])
        ->and($result['preserved'])->toEqual(SeedStaticPagesAction::OPTION_KEYS)
        ->and($options->get('privacy_policy_title'))->toBe('Custom Privacy Title')
        ->and($options->get('privacy_policy_content'))->toBe('<p>Custom privacy body</p>')
        ->and($options->get('terms_of_use_title'))->toBe('Custom Terms Title')
        ->and($options->get('terms_of_use_content'))->toBe('<p>Custom terms body</p>');
});

test('force overwrites all four static page keys', function (): void {
    $options = app(OptionStore::class);
    $options->set('privacy_policy_title', 'Custom Privacy Title');
    $options->set('privacy_policy_content', '<p>Custom privacy body</p>');
    $options->set('terms_of_use_title', 'Custom Terms Title');
    $options->set('terms_of_use_content', '<p>Custom terms body</p>');

    $result = seedStaticPagesAction()->handle(force: true);
    $defaults = mlhubStaticPagesDefaults();

    expect($result['updated'])->toEqual(SeedStaticPagesAction::OPTION_KEYS)
        ->and($result['preserved'])->toBe([])
        ->and($options->get('privacy_policy_title'))->toBe($defaults['privacy_policy_title'])
        ->and($options->get('privacy_policy_content'))->toBe($defaults['privacy_policy_content'])
        ->and($options->get('terms_of_use_title'))->toBe($defaults['terms_of_use_title'])
        ->and($options->get('terms_of_use_content'))->toBe($defaults['terms_of_use_content']);
});

test('repeated execution does not duplicate option names', function (): void {
    seedStaticPagesAction()->handle(force: true);
    seedStaticPagesAction()->handle(force: true);

    foreach (SeedStaticPagesAction::OPTION_KEYS as $key) {
        expect(DB::table('options')->where('name', $key)->count())->toBe(1);
    }

    expect(DB::table('options')->whereIn('name', SeedStaticPagesAction::OPTION_KEYS)->count())->toBe(4);
});

test('throws when options table is missing', function (): void {
    Schema::dropIfExists('options');

    seedStaticPagesAction()->handle();
})->throws(RuntimeException::class, 'Cannot seed MLHUB static pages: the options table does not exist.');

test('invalidates option cache through OptionStore set', function (): void {
    $options = app(OptionStore::class);
    $key = 'privacy_policy_content';

    Cache::forever("options.{$key}", 'stale-legacy');
    Cache::forever("options.v2.{$key}", ['exists' => true, 'value' => 'stale-v2']);

    expect(Cache::get("options.{$key}"))->toBe('stale-legacy');
    expect(Cache::get("options.v2.{$key}"))->toBe(['exists' => true, 'value' => 'stale-v2']);

    $options->set($key, 'fresh-after-invalidate');

    expect(Cache::has("options.{$key}"))->toBeFalse();
    expect(Cache::has("options.v2.{$key}"))->toBeFalse();

    expect(DB::table('options')->where('name', $key)->value('value'))->toBe('fresh-after-invalidate');
    expect($options->get($key))->toBe('fresh-after-invalidate');
});

test('privacy and terms data include required verification phrases', function (): void {
    $defaults = mlhubStaticPagesDefaults();

    expect($defaults)->toHaveKeys(SeedStaticPagesAction::OPTION_KEYS)
        ->and($defaults['privacy_policy_content'])->toContain('Google API Services User Data Policy')
        ->and($defaults['privacy_policy_content'])->toContain('Limited Use')
        ->and($defaults['terms_of_use_content'])->toContain('Điều khoản Sử dụng');
});

test('MLHUBInstallCommand invokes SeedStaticPagesAction with force true after install seeding', function (): void {
    $source = file_get_contents((new ReflectionClass(MLHUBInstallCommand::class))->getFileName());

    $seedPos = strpos($source, "MLHUBArtisanTasks::seed(\$this, 'install')");
    $staticPos = strpos($source, '$seedStaticPages->handle(force: true)');
    $optimizePos = strpos($source, 'MLHUBArtisanTasks::optimize($this)');

    expect($seedPos)->not->toBeFalse()
        ->and($staticPos)->not->toBeFalse()
        ->and($optimizePos)->not->toBeFalse()
        ->and($seedPos)->toBeLessThan($staticPos)
        ->and($staticPos)->toBeLessThan($optimizePos);

    $method = new ReflectionMethod(MLHUBInstallCommand::class, 'handle');
    $params = $method->getParameters();

    expect($params)->not->toBeEmpty()
        ->and($params[0]->getName())->toBe('seedStaticPages')
        ->and($params[0]->getType()?->getName())->toBe(SeedStaticPagesAction::class);
});
