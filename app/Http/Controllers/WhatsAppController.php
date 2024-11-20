<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WhatsAppController extends Controller
{
    public function new_message(Request $request){
        dsd($request->all());
    }
}