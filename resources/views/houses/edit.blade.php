@extends('layouts.app')

@section('title','Редактирование дома')

@section('content')
<div class="container mx-auto py-6">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-semibold">Редактирование дома #{{ $house->house_id }}</h1>

        <form method="POST" action="{{ route('houses.destroy', $house) }}"
              onsubmit="return confirm('Удалить дом #{{ $house->house_id }}?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="px-4 py-2 border rounded text-red-600">Удалить</button>
        </form>
    </div>

    <form method="POST" action="{{ route('houses.update', $house) }}" enctype="multipart/form-data" class="bg-white rounded shadow p-6">
        @method('PUT')
        @include('houses._form', ['house' => $house, 'users' => $users])
    </form>
</div>
@endsection
