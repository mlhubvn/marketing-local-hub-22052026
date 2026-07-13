<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Modules\AdminSettings\Support\OptionStore;
use Modules\CustomMLHUB\Actions\SeedStaticPagesAction;

beforeEach(function (): void {
    Schema::dropIfExists('options');

    Schema::create('options', function (Blueprint $table): void {
        $table->id();
        $table->string('name', 255);
        $table->longText('value')->nullable();
        $table->timestamps();
        $table->unique('name', 'options_name_unique');
    });

    Cache::flush();

    app(OptionStore::class)->set(
        config('themes.areas.guest.option_key', 'frontend_theme'),
        config('mlhub.site.guest_theme', 'mlhubfrontend')
    );

    app(SeedStaticPagesAction::class)->handle(force: true);

    if (method_exists($this, 'withoutVite')) {
        $this->withoutVite();
    }
});

afterEach(function (): void {
    Schema::dropIfExists('options');
    Cache::flush();
});

test('privacy policy page renders Google API verification phrases', function (): void {
    $this->get('/privacy-policy')
        ->assertOk()
        ->assertSee('Google API Services User Data Policy', false)
        ->assertSee('Limited Use', false);
});

test('terms of use page renders terms heading phrase', function (): void {
    $this->get('/terms-of-use')
        ->assertOk()
        ->assertSee('Điều khoản Sử dụng', false);
});
