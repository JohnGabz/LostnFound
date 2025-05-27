@extends('layouts.admin')

@section('content')
<div class="container">
    <h1>Edit User</h1>

    <form action="{{ route('admin.users.update', $user->user_id) }}" method="POST">
        @csrf @method('PUT')

        <div class="form-group">
            <label>Name</label>
            <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control">
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control">
        </div>

        <div class="form-group">
            <label>Role</label>
            <input type="text" name="role" value="{{ old('role', $user->role) }}" class="form-control">
        </div>

        <button type="submit" class="btn btn-success mt-2">Update</button>
    </form>
</div>
@endsection
