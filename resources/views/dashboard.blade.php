@extends('layouts.auth')
@section('title', 'Dashboard')
@section('content')
<section class="card">
    <h1>Welcome, {{ auth()->user()->name }}</h1>
    <p>Signed in as {{ auth()->user()->role }}.</p>
    @can('manage-staff')<p><a class="button" href="{{ route('admin.staff.index') }}">Manage staff accounts</a></p>@endcan
    <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit">Sign out</button></form>
</section>
@endsection
