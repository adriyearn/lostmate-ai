@php
    $code ??= 500;
    $title ??= 'Something went wrong';
    $message ??= 'Please try again in a moment.';
@endphp
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $code }} - {{ $title }} | LostMate AI</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
        <div class="d-flex justify-content-center align-items-center" style="min-height: 100vh;">
            <div class="text-center px-3">
                <div class="display-1 fw-bold text-secondary">{{ $code }}</div>
                <h1 class="h4 mb-2">{{ $title }}</h1>
                <p class="text-muted mb-4">{{ $message }}</p>
                <a href="{{ url('/') }}" class="btn btn-primary">Back to LostMate AI</a>
            </div>
        </div>
    </body>
</html>
