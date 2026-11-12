<?php

namespace App\Http\Controllers;

use App\Models\BorrowingRequest;
use App\Models\Category;
use App\Models\Equipment;
use App\Models\User;

class HomeController extends Controller
{
    public function index()
    {
        // Real numbers from the database for the landing page highlights
        return view('landing', ['highlights' => [
            'units'      => (int) Equipment::where('status', 'active')->sum('available_quantity'),
            'categories' => Category::count(),
            'borrowers'  => User::borrowers()->count(),
            'returned'   => BorrowingRequest::where('status', 'returned')->count(),
        ]]);
    }
}
