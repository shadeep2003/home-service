<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PublicPageController
{
    public function home(): View { return view('welcome'); }
    public function services(): View { return view('public.services'); }
    public function about(): View { return view('public.about'); }
    public function contact(): View { return view('public.contact'); }
}
