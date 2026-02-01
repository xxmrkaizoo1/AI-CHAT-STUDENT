<?php

namespace App\Http\Controllers;

class StudentDashboardController extends Controller
{
    public function index()
    {
        return view('dashboards.student');
    }
}
