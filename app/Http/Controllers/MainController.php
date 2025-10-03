<?php

namespace App\Http\Controllers;

use App\Service\ReminderService;
use Illuminate\Http\Request;

class MainController extends Controller
{
    /**
     * Handle the incoming request.
     */
    
    protected $rervice;

    public function __construct(ReminderService $rervice)
    {
        $this->rervice = $rervice;
    }

    public function __invoke(Request $request)
    {
        $this->rervice->run();
    }
}
