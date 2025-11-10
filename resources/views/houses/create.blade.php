@extends('layouts.app')

@section('title','Новый дом')

@section('content')
<div class="container mx-auto py-6">
    <h1 class="text-2xl font-semibold mb-4">Создание дома</h1>

    <form method="POST" action="{{ route('houses.store') }}" enctype="multipart/form-data" class="bg-white rounded shadow p-6">
        @include('houses._form', ['house' => $house, 'users' => $users])
    </form>
</div>
@endsection
