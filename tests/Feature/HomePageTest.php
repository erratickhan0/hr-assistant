<?php

test('home page embeds the product demo video', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Watch the demo', false)
        ->assertSee('Collect, organise, and search candidate CVs in one place', false)
        ->assertSee('youtube.com/embed/n5-86E0vYv0', false)
        ->assertSee('youtube.com/watch?v=n5-86E0vYv0', false)
        ->assertSee('Watch on YouTube', false)
        ->assertDontSee('youtube-nocookie.com', false)
        ->assertDontSee('start=101', false);
});
