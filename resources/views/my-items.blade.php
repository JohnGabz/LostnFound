@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    <ul class="nav nav-tabs mb-4" id="itemsTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="lost-tab" data-toggle="tab" href="#lost" role="tab"
                aria-controls="lost" aria-selected="true">
                Lost <span class="badge badge-danger">{{ $lostItems->count() }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="found-tab" data-toggle="tab" href="#found" role="tab"
                aria-controls="found" aria-selected="false">
                Found <span class="badge badge-info">{{ $foundItems->count() }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="claimed-tab" data-toggle="tab" href="#claimed" role="tab"
                aria-controls="claimed" aria-selected="false">
                Claimed <span class="badge badge-success">{{ $claimedItems->count() }}</span>
            </a>
        </li>
    </ul>

    <div class="tab-content" id="itemsTabsContent">
        {{-- Lost Items --}}
        <div class="tab-pane fade show active" id="lost" role="tabpanel" aria-labelledby="lost-tab">
            @include('partials.items-list', ['items' => $lostItems, 'badge' => 'danger'])
        </div>

        {{-- Found Items --}}
        <div class="tab-pane fade" id="found" role="tabpanel" aria-labelledby="found-tab">
            @include('partials.items-list', ['items' => $foundItems, 'badge' => 'info'])
        </div>

        {{-- Claimed Items --}}
        <div class="tab-pane fade" id="claimed" role="tabpanel" aria-labelledby="claimed-tab">
            @include('partials.items-list', ['items' => $claimedItems, 'badge' => 'success'])
        </div>
    </div>
</div>
@endsection
