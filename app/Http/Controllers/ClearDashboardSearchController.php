<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ClearDashboardSearchController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $request->session()->forget([
            'search_query',
            'search_result_ids',
            'search_mode',
            'search_evidence',
            'search_answer',
        ]);

        return redirect()->route('dashboard');
    }
}
