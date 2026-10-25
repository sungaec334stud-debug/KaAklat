@extends('layouts.auth')
@section('title', 'Staff accounts')
@section('content')
<section class="card">
    <h1>Staff account management</h1>
    <p><a href="{{ route('dashboard') }}">Back to dashboard</a></p>
    @if ($errors->any())<div class="error"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <h2>Create staff account</h2>
    <p class="muted">Set an initial password and share it with the staff member securely. They must verify their email before signing in.</p>
    <form method="POST" action="{{ route('admin.staff.store') }}">@csrf
        <label for="name">Name</label><input id="name" name="name" value="{{ old('name') }}" required>
        <label for="email">Email</label><input id="email" name="email" type="email" value="{{ old('email') }}" required>
        <label for="role">Role</label><select id="role" name="role" required><option value="librarian">Librarian</option><option value="admin">Administrator</option></select>
        <label for="password">Initial password (12+ characters)</label><input id="password" name="password" type="password" minlength="12" required autocomplete="new-password">
        <label for="password_confirmation">Confirm initial password</label><input id="password_confirmation" name="password_confirmation" type="password" minlength="12" required autocomplete="new-password">
        <button type="submit">Create account</button>
    </form>
    <h2>Existing staff</h2>
    <table><thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Account</th></tr></thead><tbody>
    @forelse ($staff as $member)<tr><td>{{ $member->name }}</td><td>{{ $member->email }}</td><td>{{ $member->role }}</td><td>{{ $member->is_active && $member->hasVerifiedEmail() ? 'Active' : 'Awaiting verification / inactive' }}</td></tr>
    @empty<tr><td colspan="4">No staff accounts.</td></tr>@endforelse
    </tbody></table>
</section>
@endsection
