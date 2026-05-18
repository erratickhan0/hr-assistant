<?php

test('faq page is available to guests', function () {
    $this->get(route('pages.faq'))
        ->assertOk()
        ->assertSee('Frequently asked questions', false)
        ->assertSee('What is the difference between View and Download?', false);
});

test('how it works page links to faq', function () {
    $this->get(route('pages.how-it-works'))
        ->assertOk()
        ->assertSee('See the FAQ', false);
});
