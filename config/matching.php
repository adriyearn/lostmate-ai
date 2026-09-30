<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Fake AI Matching
    |--------------------------------------------------------------------------
    |
    | When true, MatchingService skips the OpenAI API call and instead scores
    | candidates by simple keyword overlap. Lets you test the whole matching
    | flow (queue, notifications, Possible Matches page) without spending API
    | credits or needing an OPENAI_API_KEY set.
    |
    */
    'fake' => env('AI_MATCHING_FAKE', false),

    'max_candidates' => 15,
    'candidate_window_days' => 30,
    'min_score_to_save' => 50,
    'min_score_to_notify' => 70,

];
