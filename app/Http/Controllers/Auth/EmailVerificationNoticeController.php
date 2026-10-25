<?php

namespace App\Http\Controllers\Auth;

use Illuminate\View\View;

class EmailVerificationNoticeController
{
    public function __invoke(): View
    {
        return view('auth.verify-email');
    }
}
