@extends('layout')

@section('title', 'Новый дом')

@section('main_content')
<div style="max-width: 1200px; margin: 0 auto; padding: 24px;">
    <h1 style="font-size: 28px; font-weight: 600; margin-bottom: 24px; color: #1c1c1c;">Создание дома</h1>

    <form method="POST" action="{{ route('houses.store') }}" enctype="multipart/form-data" style="background: #fff; border-radius: 12px; padding: 32px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
        @include('houses._form', ['house' => $house, 'users' => $users, 'rentTypes' => $rentTypes, 'houseTypes' => $houseTypes, 'currentUser' => $currentUser])
    </form>
</div>
@endsection
