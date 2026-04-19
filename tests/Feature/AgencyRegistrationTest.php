<?php

use App\Models\Organization;
use App\Models\User;

test('guest can register an organization and land on dashboard', function () {
    $response = $this->from('/register')->post('/register', [
        'organization_name' => 'Acme HR',
        'organization_slug' => 'acme-hr',
        'admin_name' => 'Alex Admin',
        'email' => 'alex@acme.test',
        'password' => 'password-secure',
        'password_confirmation' => 'password-secure',
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticated();

    expect(Organization::query()->where('slug', 'acme-hr')->exists())->toBeTrue();
    expect(User::query()->where('email', 'alex@acme.test')->exists())->toBeTrue();
});

test('hr user can log in with agency slug email and password', function () {
    $organization = Organization::factory()->create(['slug' => 'login-test']);
    $user = User::factory()->for($organization)->create([
        'email' => 'hr@example.test',
        'password' => 'secret-pass-1',
    ]);

    $response = $this->post('/login', [
        'organization_slug' => 'login-test',
        'email' => 'hr@example.test',
        'password' => 'secret-pass-1',
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticatedAs($user);
});
