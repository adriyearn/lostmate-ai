@include('errors.minimal', [
    'code' => 429,
    'title' => 'Slow down a little',
    'message' => "You're doing that too often. Please wait a minute and try again.",
])
