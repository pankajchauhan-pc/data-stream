<?php 
namespace App\Services;

use Illuminate\Support\Facades\Cache;

class DataStreamService
{
    public function getSubsequences($stream, $k, $top, $exclude = [])
    {
        $counts = [];
        $length = strlen($stream);
        for ($i = 0; $i <= $length - $k; $i++) {
            $subsequence = substr($stream, $i, $k);
            
            if (in_array($subsequence, $exclude)) {
                continue;
            }
            
            if (isset($counts[$subsequence])) {
                $counts[$subsequence]++;
            } else {
                $counts[$subsequence] = 1;
            }
        }
        
        arsort($counts);

        $most_frequent_subsequences = array_slice($counts, 0, $top, true);

        $subsequences = [];
        foreach ($most_frequent_subsequences as $subsequence => $count) {
            $subsequences[] = [
                'subsequence' => $subsequence,
                'count' => $count,
            ];
        }
        return $subsequences;
    }
}
