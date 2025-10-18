<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8" />
    <title>SQLite Viewer</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.4.4/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 p-8">
<div class="max-w-6xl mx-auto space-y-6">

    <div class="bg-white shadow rounded p-6">
        <h1 class="text-2xl font-bold mb-4">Выбор таблицы</h1>

        @if (session('status'))
            <div class="mb-4 rounded border border-green-200 bg-green-50 text-green-800 px-4 py-2">
                {{ session('status') }}
            </div>
        @endif

        <form method="GET" action="/">
            <label for="table" class="block mb-2 font-semibold">Таблица:</label>
            <select id="table" name="table" class="w-full border rounded px-3 py-2" onchange="this.form.submit()">
                <option value="">— выберите таблицу —</option>
                @foreach ($tables as $table)
                    <option value="{{ $table }}" {{ old('table', $selectedTable) === $table ? 'selected' : '' }}>
                        {{ $table }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    @if ($selectedTable)
        {{-- ФОРМА ДОБАВЛЕНИЯ --}}
        <div class="bg-white shadow rounded p-6">
            <h2 class="text-xl font-semibold mb-4">Добавить запись в «{{ $selectedTable }}»</h2>

            <form method="POST" action="/">
                @csrf
                <input type="hidden" name="table" value="{{ $selectedTable }}">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @php
                        $blocked = ['id','created_at','updated_at','deleted_at'];
                    @endphp

                    @foreach ($columns as $col)
                        @continue(in_array($col->name, $blocked, true))

                        <div>
                            <label class="block text-sm font-medium mb-1">
                                {{ $col->name }}
                                <span class="text-xs text-gray-500">
                                    ({{ $col->type ?: 'TEXT' }})
                                    @if ($col->notnull) • required @endif
                                </span>
                            </label>
                            <input
                                type="text"
                                name="{{ $col->name }}"
                                value="{{ old($col->name) }}"
                                class="w-full border rounded px-3 py-2"
                                @if ($col->notnull && $col->dflt_value === null) required @endif
                                placeholder="Введите значение"
                            >
                        </div>
                    @endforeach
                </div>

                <div class="mt-4">
                    <button class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                        Добавить запись
                    </button>
                </div>
            </form>
        </div>

        {{-- ПРОСМОТР ДАННЫХ --}}
        <div class="bg-white shadow rounded p-6">
            <h2 class="text-xl font-semibold mb-3">Первые 10 строк из «{{ $selectedTable }}»</h2>

            @if ($rows->isEmpty())
                <p class="text-gray-500">Нет данных для отображения.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full border border-gray-300 rounded">
                        <thead>
                            <tr class="bg-gray-200">
                                @foreach(array_keys((array)$rows->first()) as $col)
                                    <th class="border px-3 py-2 text-left text-sm font-semibold">{{ $col }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rows as $row)
                                <tr class="odd:bg-white even:bg-gray-50">
                                    @foreach ((array)$row as $val)
                                        <td class="border px-3 py-2 text-sm">
                                            {{ is_null($val) ? '—' : $val }}
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @endif

</div>
</body>

</html>