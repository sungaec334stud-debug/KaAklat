@extends('layouts.auth')
@section('title', 'Sign in')
@section('content')
<section class="card">
    <h1>Sign in to KaAklat</h1>
    @if ($errors->any())<div class="error"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <form method="POST" action="{{ route('login.store') }}">@csrf
        <label for="email">Email</label><input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="username">
        <label for="password">Password</label><input id="password" name="password" type="password" required autocomplete="current-password">
        <label><input name="remember" type="checkbox" value="1" style="display:inline;width:auto"> Remember me</label>
        <button type="submit">Sign in</button>
    </form>
    <p><a href="{{ route('verification.notice') }}">Need an email verification link?</a></p>
    <p>Student? <a href="{{ route('register') }}">Create an account</a></p>
</section>
@endsection
