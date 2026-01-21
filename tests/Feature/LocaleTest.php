<?php

beforeEach(function () {
    app()->setLocale('id');
});

it('has the correct default locale after setting it', function () {
    expect(app()->getLocale())->toBe('id');
});

it('can translate simple strings', function () {
    expect(__('Pending'))->toBe('Menunggu');
    expect(__('Approved'))->toBe('Disetujui');
});
