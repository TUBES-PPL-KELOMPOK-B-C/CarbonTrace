<?php

namespace App\Http\Controllers;

use App\Services\RingkasanDashboard111;
use Illuminate\Http\Request;
use Illuminate\View\View;

// KF-21 Dashboard Jejak Karbon: hanya menampilkan, tidak mengelola data sendiri.
class Dashboard111 extends Controller
{
    public function index(Request $request, RingkasanDashboard111 $ringkasan): View
    {
        return view('dashboard.index-111', [
            'pengguna' => $request->user(),
            'ringkasan' => $ringkasan->untuk($request->user()),
        ]);
    }
}
