@extends('layouts.app')

@section('title','Дома')

@section('content')
    <div class="container mx-auto py-6">
        <livewire:houses-page :search-input="$searchInput ?? ''" />
    </div>
@endsection
