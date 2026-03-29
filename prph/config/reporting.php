<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Report Flag Threshold
    |--------------------------------------------------------------------------
    |
    | This value controls how many reports a post must receive
    | before it is automatically flagged. You can override this
    | in your .env file using REPORT_FLAG_THRESHOLD.
    |
    */

    'flag_threshold' => env('REPORT_FLAG_THRESHOLD', 5),

];
