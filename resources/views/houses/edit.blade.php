@extends('layout')

@section('title', 'Редактирование дома')

@section('main_content')
<div style="max-width: 1200px; margin: 0 auto; padding: 24px;">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
        <h1 style="font-size: 28px; font-weight: 600; color: #1c1c1c;">Редактирование дома #{{ $house->house_id }}</h1>

        <form method="POST" action="{{ route('houses.destroy', $house) }}"
              onsubmit="return confirm('Удалить дом #{{ $house->house_id }}?')" style="margin: 0;">
            @csrf
            @method('DELETE')
            <button type="submit" style="padding: 9px 18px; border: 1px solid #e0e0e0; border-radius: 8px; background: #fff; color: #dc2626; cursor: pointer; font-size: 14px; font-weight: 500; transition: 0.2s ease;">
                Удалить
            </button>
        </form>
    </div>

    <form method="POST" action="{{ route('houses.update', $house) }}" enctype="multipart/form-data" style="background: #fff; border-radius: 12px; padding: 32px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
        @method('PUT')
        @include('houses._form', ['house' => $house, 'users' => $users, 'rentTypes' => $rentTypes, 'houseTypes' => $houseTypes, 'currentUser' => $currentUser])
    </form>
</div>
@endsection
