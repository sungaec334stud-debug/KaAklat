@extends('layouts.auth')
@section('title', 'Verify your email')
@section('content')
<section class="card">
    <h1>Verify your email address</h1>
    <p>After registering, check your inbox for a signed verification link. The account remains inactive until that link is used.</p>
    <p>If you need another link, enter the email address used for registration.</p>
    @if ($errors->any())<div class="error"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <form method="POST" action="{{ route('verification.send') }}">@csrf
        <label for="email">Email</label><input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email">
        <button type="submit">Send verification link</button>
    </form>
    <p><a href="{{ route('login') }}">Back to sign in</a></p>
</section>
@endsection
