@extends('layouts.auth')
@section('title', 'Student registration')
@section('content')
<section class="card">
    <h1>Create a student account</h1>
    <p class="muted">Use your Universidad de Dagupan email. Your account will be activated after email verification.</p>
    @if ($errors->any())<div class="error"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <form method="POST" action="{{ route('register.store') }}">@csrf
        <label for="name">Full name</label><input id="name" name="name" value="{{ old('name') }}" required autocomplete="name">
        <label for="student_id">Student ID</label><input id="student_id" name="student_id" value="{{ old('student_id') }}" required autocomplete="off" aria-describedby="student-id-note"><small id="student-id-note" class="muted">Enter the student ID issued by the university.</small>
        <label for="email">University email</label><input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email">
        <label for="password">Password</label><input id="password" name="password" type="password" required minlength="12" autocomplete="new-password">
        <label for="password_confirmation">Confirm password</label><input id="password_confirmation" name="password_confirmation" type="password" required minlength="12" autocomplete="new-password">
        <button type="submit">Register</button>
    </form>
    <p>Already registered? <a href="{{ route('login') }}">Sign in</a></p>
    <p>Waiting for your verification email? <a href="{{ route('verification.notice') }}">Request another verification link</a>.</p>
</section>
@endsection
