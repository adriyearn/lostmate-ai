@include('errors.minimal', [
    'code' => 403,
    'title' => "You don't have access to this",
    'message' => $exception->getMessage() ?: "You don't have permission to view this page.",
])
