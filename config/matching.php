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

    /*
    |--------------------------------------------------------------------------
    | Compare Photos (optional, off by default)
    |--------------------------------------------------------------------------
    |
    | When true (and fake mode is off), the AI also receives the FIRST photo
    | of the report and of each candidate, so it can compare the actual
    | objects. Needs a vision-capable model (e.g. gpt-4o-mini; for Ollama use
    | a vision model such as llava - llama3.2:3b cannot see images).
    |
    | Photos are sent at "low" detail, which costs a small fixed amount per
    | image. Text details still follow every rule in CLAUDE.md - hidden
    | details, names, emails, and phone numbers are never sent.
    |
    */
    'use_photos' => env('AI_MATCHING_PHOTOS', false),
    'max_photo_bytes' => 4 * 1024 * 1024,

    'max_candidates' => 15,
    'candidate_window_days' => 30,
    'min_score_to_save' => 50,
    'min_score_to_notify' => 70,

];
