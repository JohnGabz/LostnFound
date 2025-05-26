@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Claimer Profile</h2>

    <div class="card">
        <div class="card-body">
            <p><strong>Name:</strong> {{ $user->name }}</p>
            <p><strong>Email:</strong> {{ $user->email }}</p>
            {{-- Add more fields as needed --}}
        </div>
    </div>
</div>
@endsection
