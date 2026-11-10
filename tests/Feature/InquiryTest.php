<?php

use App\Enums\InquiryType;
use App\Models\Inquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('a sell inquiry can be stored without a car', function () {
    $inquiry = Inquiry::factory()->sell()->create();

    expect($inquiry->car)->toBeNull();
    expect($inquiry->type)->toBe(InquiryType::Sell);
});
