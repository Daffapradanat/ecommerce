<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class LanguageController extends Controller
{
   public function changeLanguage(Request $request)
   {
       $request->validate([
           'locale' => 'required|in:en,id',
       ]);

       App::setLocale($request->locale);

       return response()->json([
           'message' => __('messages.language_changed'),
           'locale' => App::getLocale(),
       ]);
   }
}
