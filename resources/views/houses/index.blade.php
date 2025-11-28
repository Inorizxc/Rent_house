@extends('layout')

@section('title','Дома')

@section('main_content')
    <div class="houses-page-wrapper">
        <livewire:houses-page :search-input="$searchInput ?? ''" />
    </div>
@endsection
