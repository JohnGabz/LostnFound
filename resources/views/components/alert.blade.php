@props(['type' => 'info', 'message'])

<div class="alert alert-{{ $type }} alert-dismissible fade show" role="alert">
    {{ $message }}
</div>
