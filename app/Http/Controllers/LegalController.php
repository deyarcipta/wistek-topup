<?php

namespace App\Http\Controllers;

class LegalController extends Controller
{
    /**
     * Display Refund & Cancellation Policy page
     */
    public function refund()
    {
        return view('legal.refund');
    }

    /**
     * Display Terms & Conditions page
     */
    public function terms()
    {
        return view('legal.terms');
    }

    /**
     * Display Privacy Policy page
     */
    public function privacy()
    {
        return view('legal.privacy');
    }
}
