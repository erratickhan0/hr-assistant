<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\StoreRegisteredOrganizationRequest;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RegisteredOrganizationController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.register-organization');
    }

    public function store(StoreRegisteredOrganizationRequest $request): RedirectResponse
    {
        $slugInput = $request->validated('organization_slug');
        $slug = $slugInput !== null && $slugInput !== ''
            ? Str::slug($slugInput)
            : Str::slug($request->validated('organization_name'));

        $baseSlug = $slug;
        while (Organization::query()->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.Str::lower(Str::random(3));
        }

        $user = DB::transaction(function () use ($request, $slug): User {
            $organization = Organization::query()->create([
                'name' => $request->validated('organization_name'),
                'slug' => $slug,
                'settings' => null,
            ]);

            return User::query()->create([
                'organization_id' => $organization->id,
                'name' => $request->validated('admin_name'),
                'email' => $request->validated('email'),
                'password' => $request->validated('password'),
                'role' => 'hr',
            ]);
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('registered_slug', $user->organization->slug);
    }
}
