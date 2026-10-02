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
        {{-- Error pages load Bootstrap from a CDN so they still work if the Vite build is missing. --}}
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,800&family=JetBrains+Mono:wght@700&family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
        <style>
            body {
                font-family: 'Plus Jakarta Sans', sans-serif;
                color: #3d3a33;
                background-color: #f7f3eb;
                background-image: radial-gradient(rgba(26, 25, 23, 0.07) 1px, transparent 1.2px);
                background-size: 22px 22px;
            }
            /* The error code is printed on a big "lost" claim tag. */
            .err-tag {
                display: inline-block;
                position: relative;
                padding: 1.25rem 2.5rem 1.25rem 3.5rem;
                background: #fffdf8;
                border: 2px solid #1a1917;
                border-radius: 8px 20px 20px 8px;
                box-shadow: 6px 6px 0 #ff6a2b;
                transform: rotate(-4deg);
                margin-bottom: 2rem;
            }
            .err-tag::before {
                content: '';
                position: absolute;
                left: 1.1rem;
                top: 50%;
                width: 16px;
                height: 16px;
                margin-top: -8px;
                border-radius: 50%;
                border: 2px solid #1a1917;
                background: #f7f3eb;
            }
            .err-code { font-family: 'Bricolage Grotesque', sans-serif; font-weight: 800; font-size: 5rem; line-height: 1; color: #1a1917; letter-spacing: -0.05em; }
            .err-label { font-family: 'JetBrains Mono', monospace; font-size: 0.7rem; letter-spacing: 0.1em; text-transform: uppercase; color: #6f6a5f; }
            h1 { font-family: 'Bricolage Grotesque', sans-serif; font-weight: 800; color: #1a1917; letter-spacing: -0.03em; }
            .btn-lm {
                font-weight: 700;
                color: #1a1917;
                background: #ff6a2b;
                border: 1.5px solid #1a1917;
                border-radius: 12px;
                padding: 0.65rem 1.25rem;
                box-shadow: 3px 3px 0 #1a1917;
            }
            .btn-lm:hover { color: #1a1917; background: #ff7d45; }
            .btn-lm:active { transform: translate(2px, 2px); box-shadow: 1px 1px 0 #1a1917; }
        </style>
    </head>
    <body>
        <div class="d-flex justify-content-center align-items-center" style="min-height: 100vh;">
            <div class="text-center px-3">
                <div class="err-tag">
                    <div class="err-label">Error tag</div>
                    <div class="err-code">{{ $code }}</div>
                </div>
                <h1 class="h3 mb-2">{{ $title }}</h1>
                <p class="mb-4" style="color:#6f6a5f;">{{ $message }}</p>
                <a href="{{ url('/') }}" class="btn btn-lm">Back to LostMate AI</a>
            </div>
        </div>
    </body>
</html>
