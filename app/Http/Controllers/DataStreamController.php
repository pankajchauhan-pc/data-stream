<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\DataStreamService;
use Illuminate\Support\Facades\Cache;

class DataStreamController extends Controller
{
    function analyze(Request $request){
        
        $request->validate([
            'k' => 'required|integer|min:1',
            'top' => 'required|integer|min:1',
            'exclude' => 'nullable|array',
        ]);

        $stream = $request->input('stream'); // A long string of characters or numbers to analyze (this could be millions of characters).

        if (!$stream && $request->hasFile('stream')) {
            $file = $request->file('stream');
            $stream = file_get_contents($file->getRealPath());
        }

        // i have made custom validation 
        if (!$stream) {
            return response()->json(['message' => 'The given data was invalid.', 'errors' => ['stream' => ['stream is required']]], 422);  
        }

        $k = $request->input('k'); // Integer specifying the size of the subsequences to analyze.
        $top = $request->input('top'); // Integer specifying how many of the most frequent subsequences to return.
        $exclude = $request->input('exclude', []); // Optional array of subsequences to ignore (e.g., ["AAA"]). if not found then pass blank array
        
        $cache = md5($stream.$k.$top.json_encode($exclude));
        
        $subsequences_from_cache = Cache::get($cache);

        if ($subsequences_from_cache) {
            return response()->json(['message' => 'From Cache','data' => $subsequences_from_cache]); // i have added message to identify that data come from cache
        }
        $dss = new DataStreamService();
        $subsequences = $dss->getSubsequences($stream,$k,$top,$exclude);
        Cache::put($cache, $subsequences, now()->addMinutes(1)); // change minutes as per your need
        return response()->json(['data' => $subsequences]);
    }
}
