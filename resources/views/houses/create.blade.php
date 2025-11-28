@extends('layout')

@section('title', 'Новый дом')

@section('main_content')
<div class="house-form-wrapper">
    <h1 class="house-form-title">Создание дома</h1>

    <form method="POST" action="{{ route('houses.store') }}" enctype="multipart/form-data" class="house-form">
        @include('houses._form', ['house' => $house, 'users' => $users, 'rentTypes' => $rentTypes, 'houseTypes' => $houseTypes, 'currentUser' => $currentUser])
    </form>
</div>
@endsection
