<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Просмотр таблиц SQLite</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.4.4/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 p-8">

    <div class="max-w-5xl mx-auto bg-white shadow-md rounded-lg p-6">
        <h1 class="text-2xl font-bold mb-4">Просмотр таблиц SQLite</h1>

        <!-- Меню выбора таблицы -->
        <form method="GET" action="/">
            <label for="table" class="block mb-2 font-semibold">Выберите таблицу:</label>
            <select id="table" name="table" onchange="this.form.submit()"
                    class="w-full border rounded px-3 py-2">
                <option value="">-- выберите таблицу --</option>
                @foreach ($tables as $table)
                    <option value="{{ $table }}"
                        {{ $selectedTable === $table ? 'selected' : '' }}>
                        {{ $table }}
                    </option>
                @endforeach
            </select>
        </form>

        <!-- Вывод таблицы -->
        @if ($selectedTable)
            <h2 class="text-xl font-semibold mt-6 mb-3">Таблица: {{ $selectedTable }}</h2>

            @if ($rows->isEmpty())
                <p class="text-gray-500">Таблица пуста или не содержит данных.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full border border-gray-300 rounded-lg">
                        <thead class="bg-gray-200">
                            <tr>
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
        @endif
    </div>

</body>

</html>