<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/** `/` dan `/dashboard`: arahkan ke halaman utama sesuai role. */
class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        return redirect()->route($request->user()->role->homeRoute());
    }
}
