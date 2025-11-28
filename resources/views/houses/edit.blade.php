@extends('layout')

@section('title', 'Редактирование дома')

@section('main_content')
<div class="house-form-wrapper">
    <div class="house-form-header">
        <h1 class="house-form-title">Редактирование дома #{{ $house->house_id }}</h1>

        <form method="POST" action="{{ route('houses.destroy', $house) }}"
              onsubmit="return confirm('Удалить дом #{{ $house->house_id }}?')" style="margin: 0;">
            @csrf
            @method('DELETE')
            <button type="submit" class="house-form-btn-delete">
                Удалить
            </button>
        </form>
    </div>

    <div class="house-form-layout">
        <form method="POST" action="{{ route('houses.update', $house) }}" enctype="multipart/form-data" class="house-form">
            @method('PUT')
            @include('houses._form', ['house' => $house, 'users' => $users, 'rentTypes' => $rentTypes, 'houseTypes' => $houseTypes, 'currentUser' => $currentUser])
        </form>

        <div class="house-form-sticky-sidebar">
            @include('houses.partials.house-calendar', ['house' => $house])
        </div>
    </div>
</div>
@endsection
