<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'KaAklat') · KaAklat</title>
    <style>
        :root{font-family:system-ui,-apple-system,"Segoe UI",sans-serif;color:#172033;background:#f4f6fb}*{box-sizing:border-box}body{margin:0}header{background:#183b56;color:#fff;padding:1rem 1.5rem}header a{color:#fff;text-decoration:none;font-weight:700}.wrap{width:min(100% - 2rem,760px);margin:2.5rem auto}.card{background:#fff;border:1px solid #dce3ec;border-radius:12px;padding:clamp(1.25rem,4vw,2rem);box-shadow:0 8px 25px #102a4310}h1{margin-top:0;font-size:1.7rem}label{display:block;font-weight:600;margin:.9rem 0 .35rem}input,select{display:block;width:100%;padding:.7rem .8rem;border:1px solid #aab7c4;border-radius:7px;font:inherit}button,.button{display:inline-block;margin-top:1.2rem;background:#176b87;border:0;border-radius:7px;color:#fff;padding:.7rem 1rem;font:inherit;font-weight:650;text-decoration:none;cursor:pointer}.muted{color:#536273}.alert{padding:.8rem 1rem;border-radius:7px;margin:.75rem 0;background:#e8f5ee;color:#164d2c}.error{color:#9a1c1c;font-size:.92rem}ul{padding-left:1.3rem}table{border-collapse:collapse;width:100%;margin-top:1rem}th,td{text-align:left;padding:.65rem;border-bottom:1px solid #dce3ec}
    </style>
</head>
<body>
<header><a href="{{ route('home') }}">KaAklat · Universidad de Dagupan Library</a></header>
<main class="wrap">
    @if (session('status'))
        <div class="alert" role="status">
            @switch(session('status'))
                @case('registration-created') Check your university email for a verification link. Your account stays inactive until you verify it. @break
                @case('registration-email-failed') Your account was created, but we could not send the verification email right now. Your account stays inactive. Use the form below to request another link shortly. @break
                @case('email-verified') Email verified. You can now sign in. @break
                @case('verification-link-sent') If an unverified account matches that address, a new link has been sent. @break
                @case('logged-out') You have been signed out. @break
                @case('staff-created') Staff account created. The account must verify its email before signing in. @break
                @default {{ session('status') }}
            @endswitch
        </div>
    @endif
    @yield('content')
</main>
</body>
</html>
