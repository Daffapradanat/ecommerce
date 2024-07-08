<?php

namespace App\Http\Controllers;

class LobbyController extends Controller
{
    public function index()
    {
        return view('lobby.index');
    }
}
