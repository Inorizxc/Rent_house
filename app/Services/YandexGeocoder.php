<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class YandexGeocoder{
    private ?string $apiKey = null;

    /**
     * Конструктор класса
     * 
     * @param string|null $apiKey API ключ Yandex Geocoder. Если не указан, используется ключ по умолчанию
     */
    public function __construct(?string $apiKey = null)
    {
        $this->apiKey = $apiKey ?? 'a2cd05de-c1e4-457b-8092-a8b0ebd9db10';
    }
    
    /**
     * Получает API ключ, инициализируя его при необходимости
     */
    private function getApiKey(): string
    {
        if ($this->apiKey === null) {
            $this->apiKey = 'a2cd05de-c1e4-457b-8092-a8b0ebd9db10';
        }
        return $this->apiKey;
    }

    /**
     * Нормализует адрес для лучшего распознавания геокодером
     * Использует более мягкую нормализацию для полных адресов
     * 
     * @param string $address Адрес для нормализации
     * @return string Нормализованный адрес
     */
    private function normalizeAddress(string $address): string
    {
        // Убираем лишние пробелы
        $address = preg_replace('/\s+/', ' ', trim($address));
        
        // Если адрес уже содержит город и выглядит полным, используем его с минимальной нормализацией
        $hasCity = preg_match('/\b(?:Саратов|Москва|Санкт-Петербург|Петербург|СПб)\b/ui', $address);
        $hasStreet = preg_match('/\b(?:ул\.?|улица|проспект|пр\.?|пр-т|переулок|пер\.?|бульвар|бул\.?|имени|им\.?)\b/ui', $address);
        $hasNumber = preg_match('/\d+/', $address);
        
        // Если адрес выглядит полным (есть город, улица и номер), используем его почти как есть
        if ($hasCity && ($hasStreet || $hasNumber) && strlen($address) > 10) {
            // Минимальная нормализация: убираем только лишние пробелы и нормализуем запятые
            $address = preg_replace('/\s*,\s*/u', ', ', $address);
            $address = preg_replace('/\s+/u', ' ', $address);
            return trim($address);
        }
        
        // Для коротких адресов используем более агрессивную нормализацию
        // Заменяем запятые на пробелы
        $address = str_replace(',', ' ', $address);
        
        // Убираем слова типа "улица", "проспект", "дом" и их сокращения
        // Но сохраняем "имени" и "им." для улиц типа "улица имени Н.В. Исаева"
        $address = preg_replace('/\b(?:ул\.?|улица|проспект|пр\.?|пр-т|переулок|пер\.?|бульвар|бул\.?|дом|д\.?)\s*/ui', '', $address);
        
        // Убираем "Россия" если есть город
        if ($hasCity) {
            $address = preg_replace('/\bРоссия\b/ui', '', $address);
        }
        
        // Убираем все пробелы, точки и другие знаки препинания
        // Оставляем только буквы (включая кириллицу), цифры
        $address = preg_replace('/[^\p{L}\p{N}]/u', '', $address);
        
        // Если нет города в начале и адрес короткий, добавляем "Саратов" в начало
        // Но только если это похоже на адрес улицы (содержит число)
        if (!preg_match('/^[Сс]аратов/ui', $address) && 
            preg_match('/\d+/', $address) && 
            strlen($address) < 50) {
            $address = 'Саратов' . $address;
        }
        
        return trim($address);
    }

    /**
     * Получает координаты (широта и долгота) для указанного адреса
     * 
     * @param string $address Адрес для геокодирования (например: "Саратов,Исаева,3" или "Саратов, улица Исаева, 5")
     * @return array|null Массив с ключами 'lat' (широта) и 'lng' (долгота), или null в случае ошибки
     * 
     * @example
     * $geocoder = new YandexGeocoder();
     * $coordinates = $geocoder->getCoordinates("Саратов,Исаева,3");
     * // Возвращает: ['lat' => 51.533103, 'lng' => 46.034158]
     */
    public function getCoordinates(string $address): ?array
    {
        $originalAddress = $address;
        
        // Декодируем адрес, если он был закодирован (например, через URL или JSON)
        // Проверяем, закодирован ли адрес (содержит %)
        if (preg_match('/%[0-9A-Fa-f]{2}/', $address)) {
            $address = urldecode($address);
            // Может быть двойное кодирование, декодируем еще раз
            if (preg_match('/%[0-9A-Fa-f]{2}/', $address)) {
                $address = urldecode($address);
            }
        }
        
        // Определяем, является ли адрес полным (содержит город, улицу и номер)
        $isFullAddress = preg_match('/\b(?:Саратов|Москва|Санкт-Петербург|Петербург|СПб)\b/ui', $address) &&
                         (preg_match('/\b(?:ул\.?|улица|проспект|пр\.?|пр-т|переулок|пер\.?|бульвар|бул\.?|имени|им\.?)\b/ui', $address) ||
                          preg_match('/\d+/', $address)) &&
                         strlen($address) > 15;
        
        // Для полных адресов сначала пробуем оригинальный адрес, затем нормализованный
        $addressesToTry = [];
        if ($isFullAddress) {
            // Сначала пробуем оригинальный адрес
            $addressesToTry[] = trim($address);
            // Затем пробуем с минимальной нормализацией (убираем только "Россия")
            $minimalNormalized = preg_replace('/\bРоссия\b/ui', '', $address);
            $minimalNormalized = preg_replace('/\s*,\s*/u', ', ', $minimalNormalized);
            $minimalNormalized = preg_replace('/\s+/u', ' ', trim($minimalNormalized));
            if ($minimalNormalized !== $address) {
                $addressesToTry[] = $minimalNormalized;
            }
        }
        
        // Всегда добавляем нормализованный адрес
        $normalizedAddress = $this->normalizeAddress($address);
        if (!in_array($normalizedAddress, $addressesToTry)) {
            $addressesToTry[] = $normalizedAddress;
        }
        
        // Проверяем кеш для всех вариантов
        foreach ($addressesToTry as $addressToTry) {
            $cacheKey = 'geocoder_' . md5($addressToTry);
            try {
                $cachedResult = Cache::get($cacheKey);
                
                if ($cachedResult !== null && is_array($cachedResult) && isset($cachedResult['lat']) && isset($cachedResult['lng'])) {
                    \Log::info('Координаты получены из кеша', [
                        'original_address' => $originalAddress,
                        'cached_address' => $addressToTry,
                        'cache_key' => $cacheKey
                    ]);
                    return $cachedResult;
                }
            } catch (\Exception $e) {
                \Log::warning('Ошибка при чтении кеша', [
                    'original_address' => $originalAddress,
                    'address_to_try' => $addressToTry,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        // Пробуем получить координаты для каждого варианта адреса
        foreach ($addressesToTry as $addressToTry) {
            $coordinates = $this->tryGetCoordinatesForAddress($addressToTry, $originalAddress);
            if ($coordinates !== null) {
                return $coordinates;
            }
        }
        
        // Если ничего не сработало, возвращаем null
        \Log::warning('Не удалось получить координаты ни для одного варианта адреса', [
            'original_address' => $originalAddress,
            'addresses_tried' => $addressesToTry
        ]);
        return null;
    }
    
    /**
     * Пытается получить координаты для конкретного адреса
     * 
     * @param string $addressToTry Адрес для попытки
     * @param string $originalAddress Оригинальный адрес (для логирования)
     * @return array|null Координаты или null
     */
    private function tryGetCoordinatesForAddress(string $addressToTry, string $originalAddress): ?array
    {
        // Используем http_build_query для правильного кодирования параметров
        // PHP_QUERY_RFC3986 использует rawurlencode, который правильно обрабатывает кириллицу
        $params = [
            'apikey' => $this->getApiKey(),
            'geocode' => $addressToTry, // http_build_query правильно закодирует кириллицу
            'format' => 'json'
        ];
        
        $url = 'https://geocode-maps.yandex.ru/v1/?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
        
        try {
            // Запоминаем время начала запроса
            $startTime = microtime(true);
            $maxExecutionTime = 3; // Максимальное время выполнения запроса в секундах (уменьшено до 3)
            
            // Проверяем, сколько времени осталось до лимита PHP
            $timeLimit = ini_get('max_execution_time');
            if ($timeLimit > 0) {
                $elapsedTime = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
                $remainingTime = $timeLimit - $elapsedTime - 2; // Оставляем 2 секунды запаса
                if ($remainingTime < $maxExecutionTime) {
                    $maxExecutionTime = max(1, $remainingTime); // Минимум 1 секунда
                }
            }
            
            // Используем cURL для более надежной работы с SSL и таймаутами
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => $maxExecutionTime, // Используем вычисленное время
                CURLOPT_CONNECTTIMEOUT => 1, // Очень короткий таймаут подключения (1 секунда)
                CURLOPT_SSL_VERIFYPEER => false, // Отключаем проверку SSL для избежания ошибок
                CURLOPT_SSL_VERIFYHOST => false, // Отключаем проверку хоста для избежания ошибок
                CURLOPT_USERAGENT => 'Laravel Geocoder',
                CURLOPT_FOLLOWLOCATION => false, // Отключаем редиректы для ускорения
                CURLOPT_MAXREDIRS => 0, // Без редиректов
                CURLOPT_NOSIGNAL => 1, // Отключаем сигналы для избежания проблем с таймаутами
                CURLOPT_FRESH_CONNECT => true, // Используем новое соединение
                CURLOPT_FORBID_REUSE => true, // Не переиспользуем соединение
                CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4, // Используем только IPv4 для ускорения
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            $curlErrno = curl_errno($ch);
            $executionTime = microtime(true) - $startTime;
            
            curl_close($ch);
            
            // Проверяем, не превысили ли мы максимальное время выполнения
            if ($executionTime > $maxExecutionTime) {
                \Log::warning('Запрос к Yandex Geocoder API превысил максимальное время выполнения', [
                    'original_address' => $originalAddress,
                    'address_to_try' => $addressToTry,
                    'execution_time' => $executionTime,
                    'max_time' => $maxExecutionTime
                ]);
                return null;
            }
            
            // Обрабатываем таймауты и ошибки подключения как нормальную ситуацию
            if ($response === false || $curlErrno !== 0) {
                // Проверяем, является ли это таймаутом
                $isTimeout = ($curlErrno === CURLE_OPERATION_TIMEDOUT || 
                             $curlErrno === CURLE_OPERATION_TIMEOUTED ||
                             strpos($curlError, 'timeout') !== false ||
                             strpos($curlError, 'timed out') !== false);
            
                if ($isTimeout) {
                    \Log::warning('Таймаут при запросе к Yandex Geocoder API', [
                        'original_address' => $originalAddress,
                        'address_to_try' => $addressToTry,
                        'url' => str_replace($this->getApiKey(), '***', $url),
                        'curl_error' => $curlError,
                        'curl_errno' => $curlErrno
                    ]);
                } else {
                \Log::error('Ошибка при запросе к Yandex Geocoder API', [
                        'original_address' => $originalAddress,
                        'address_to_try' => $addressToTry,
                        'url' => str_replace($this->getApiKey(), '***', $url),
                        'curl_error' => $curlError,
                        'curl_errno' => $curlErrno,
                        'http_code' => $httpCode
                    ]);
                }
                return null;
            }
            
            if ($httpCode !== 200) {
                \Log::error('Yandex Geocoder API вернул неожиданный HTTP код', [
                    'original_address' => $originalAddress,
                    'address_to_try' => $addressToTry,
                    'url' => str_replace($this->getApiKey(), '***', $url),
                    'http_code' => $httpCode,
                    'response_preview' => substr($response, 0, 500)
                ]);
                return null;
            }
            
            // Проверяем, что ответ в формате JSON (начинается с { или [)
            // Если пришел XML (начинается с <?xml), значит format=json не сработал
            if (strpos(trim($response), '<?xml') === 0) {
                \Log::error('Yandex Geocoder вернул XML вместо JSON', [
                    'original_address' => $originalAddress,
                    'address_to_try' => $addressToTry,
                    'url' => str_replace($this->getApiKey(), '***', $url),
                    'response_preview' => substr($response, 0, 500)
                ]);
                // Попробуем распарсить XML как fallback
                $coordinates = $this->parseXmlResponse($response, $originalAddress);
                if ($coordinates) {
                    // Сохраняем результат в кеш
                    $cacheKey = 'geocoder_' . md5($addressToTry);
                    Cache::put($cacheKey, $coordinates, now()->addDays(30));
                }
                return $coordinates;
            }
            
            $data = json_decode($response, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                \Log::error('Ошибка декодирования JSON от Yandex Geocoder', [
                    'original_address' => $originalAddress,
                    'address_to_try' => $addressToTry,
                    'json_error' => json_last_error_msg(),
                    'response_preview' => substr($response, 0, 500)
                ]);
                return null;
            }
            
            // Проверяем наличие ошибок в ответе API
            if (isset($data['error'])) {
                \Log::error('Yandex Geocoder вернул ошибку', [
                    'original_address' => $originalAddress,
                    'address_to_try' => $addressToTry,
                    'error' => $data['error'],
                    'full_response' => $response
                ]);
                return null;
            }
            
            if (!isset($data['response'])) {
                \Log::warning('Неожиданный формат ответа от Yandex Geocoder', [
                    'original_address' => $originalAddress,
                    'address_to_try' => $addressToTry,
                    'response_keys' => isset($data) && is_array($data) ? array_keys($data) : 'не массив',
                    'response_preview' => is_array($data) ? json_encode(array_slice($data, 0, 3, true)) : substr($response, 0, 500)
                ]);
                return null;
            }
            
            // Проверяем наличие GeoObjectCollection
            if (!isset($data['response']['GeoObjectCollection'])) {
                \Log::warning('Ответ не содержит GeoObjectCollection', [
                    'original_address' => $originalAddress,
                    'address_to_try' => $addressToTry,
                    'response_structure' => isset($data['response']) && is_array($data['response']) ? array_keys($data['response']) : 'не массив'
                ]);
                return null;
            }
            
            $collection = $data['response']['GeoObjectCollection'];
            $metaData = $collection['metaDataProperty']['GeocoderResponseMetaData'] ?? null;
            
            // Проверяем количество найденных результатов
            $found = $metaData['found'] ?? 0;
            
            if ($found == 0 || !isset($collection['featureMember']) || empty($collection['featureMember'])) {
                \Log::warning('Yandex Geocoder не нашел результаты для адреса', [
                    'original_address' => $originalAddress,
                    'address_to_try' => $addressToTry,
                    'found' => $found,
                    'metaDataProperty' => $metaData
                ]);
                return null;
            }
            
            $geoObject = $collection['featureMember'][0]['GeoObject'];
            
            if (!isset($geoObject['Point']['pos'])) {
                \Log::warning('Геообъект не содержит координат', [
                    'original_address' => $originalAddress,
                    'address_to_try' => $addressToTry,
                    'geoObject_keys' => array_keys($geoObject),
                    'geoObject_structure' => json_encode(array_slice($geoObject, 0, 5, true))
                ]);
                return null;
            }
            
            // Координаты в формате "долгота широта" через пробел
            $pos = $geoObject['Point']['pos'];
            $parts = explode(' ', $pos);
            
            if (count($parts) !== 2) {
                \Log::warning('Неверный формат координат', [
                    'original_address' => $originalAddress,
                    'address_to_try' => $addressToTry,
                    'pos' => $pos,
                    'parts_count' => count($parts)
                ]);
                return null;
            }
            
            list($longitude, $latitude) = $parts;
            
            $coordinates = [
                'lat' => (float) $latitude,
                'lng' => (float) $longitude,
            ];
            
            // Сохраняем результат в кеш на 30 дней (координаты адреса не меняются)
            $cacheKey = 'geocoder_' . md5($addressToTry);
            Cache::put($cacheKey, $coordinates, now()->addDays(30));
            
            \Log::info('Координаты успешно получены и сохранены в кеш', [
                'original_address' => $originalAddress,
                'address_to_try' => $addressToTry,
                'coordinates' => $coordinates
            ]);
            
            return $coordinates;
        } catch (\Exception $e) {
            \Log::error('Исключение при получении координат', [
                'original_address' => $originalAddress,
                'address_to_try' => $addressToTry ?? null,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }
    
    /**
     * Парсит XML ответ от Yandex Geocoder API (fallback)
     */
    private function parseXmlResponse(string $xmlResponse, string $originalAddress): ?array
    {
        try {
            libxml_use_internal_errors(true);
            $xml = simplexml_load_string($xmlResponse);
            
            if ($xml === false) {
                $errors = libxml_get_errors();
                \Log::error('Ошибка парсинга XML от Yandex Geocoder', [
                    'original_address' => $originalAddress,
                    'xml_errors' => array_map(function($error) {
                        return $error->message;
                    }, $errors)
                ]);
                libxml_clear_errors();
                return null;
            }
            
            // Регистрируем пространства имен для XPath
            $namespaces = $xml->getNamespaces(true);
            foreach ($namespaces as $prefix => $namespace) {
                $xml->registerXPathNamespace($prefix ?: 'default', $namespace);
            }
            
            // Ищем первый GeoObject с координатами
            // Координаты находятся в <Point><pos>долгота широта</pos></Point>
            $posNodes = $xml->xpath('//gml:Point/gml:pos | //Point/pos | //*[local-name()="Point"]/*[local-name()="pos"]');
            
            if (empty($posNodes)) {
                \Log::warning('В XML ответе нет координат', [
                    'original_address' => $originalAddress
                ]);
                return null;
            }
            
            // Координаты в формате "долгота широта"
            $pos = trim((string)$posNodes[0]);
            $parts = preg_split('/\s+/', $pos);
            
            if (count($parts) !== 2) {
                \Log::warning('Неверный формат координат в XML', [
                    'original_address' => $originalAddress,
                    'pos' => $pos,
                    'parts_count' => count($parts)
                ]);
                return null;
            }
            
            list($longitude, $latitude) = $parts;
            
            $coordinates = [
                'lat' => (float) $latitude,
                'lng' => (float) $longitude,
            ];
            
            return $coordinates;
        } catch (\Exception $e) {
            \Log::error('Исключение при парсинге XML', [
                'original_address' => $originalAddress,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Получает подсказки адресов для автодополнения
     * Использует Geocoder API как основной метод, так как он надежнее работает
     * 
     * @param string $query Поисковый запрос (например: "Саратов, Исаева" или "Сар")
     * @param int $results Количество результатов (максимум 10)
     * @return array Массив подсказок в формате ['value' => 'полный адрес', 'display' => 'отображаемый адрес']
     */
    public function getAddressSuggestions(string $query, int $results = 10): array
    {
        if (empty(trim($query)) || strlen(trim($query)) < 2) {
            return [];
        }

        $query = trim($query);
        $results = max(1, min($results, 10)); // Ограничиваем от 1 до 10

        // Проверяем кеш
        $cacheKey = 'geocoder_suggest_' . md5($query . '_' . $results);
        try {
            $cachedResult = Cache::get($cacheKey);
            if ($cachedResult !== null && is_array($cachedResult)) {
                \Log::info('Подсказки адресов получены из кеша', [
                    'query' => $query,
                    'cache_key' => $cacheKey,
                    'count' => count($cachedResult)
                ]);
                return $cachedResult;
            }
        } catch (\Exception $e) {
            \Log::warning('Ошибка при чтении кеша подсказок, продолжаем запрос к API', [
                'query' => $query,
                'error' => $e->getMessage()
            ]);
        }
        
        // Используем Geocoder API как основной метод (он надежнее работает)
        $suggestions = $this->getSuggestionsViaGeocoder($query, $results);
        
        // Если получили результаты, сохраняем в кеш и возвращаем
        if (!empty($suggestions)) {
            Cache::put($cacheKey, $suggestions, now()->addDay());
            return $suggestions;
        }
        
        // Если Geocoder API не дал результатов, пробуем Suggest API (fallback)

        // Параметры для Yandex Suggest API
        // Пробуем несколько вариантов endpoint'ов
        $params = [
            'apikey' => $this->getApiKey(),
            'text' => $query,
            'type' => 'address',
            'results' => $results,
            'lang' => 'ru_RU'
        ];

        // Пробуем основной endpoint
        $url = 'https://suggest-maps.yandex.ru/v1/suggest?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
        
        \Log::info('Запрос к Yandex Suggest API', [
            'query' => $query,
            'url' => str_replace($this->getApiKey(), '***', $url)
        ]);

        try {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 3,
                CURLOPT_CONNECTTIMEOUT => 1,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_USERAGENT => 'Laravel Geocoder',
                CURLOPT_FOLLOWLOCATION => false,
                CURLOPT_MAXREDIRS => 0,
                CURLOPT_NOSIGNAL => 1,
                CURLOPT_FRESH_CONNECT => true,
                CURLOPT_FORBID_REUSE => true,
                CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            $curlErrno = curl_errno($ch);
            curl_close($ch);

            if ($response === false || $curlErrno !== 0) {
                \Log::warning('Ошибка при запросе к Yandex Suggest API', [
                    'query' => $query,
                    'curl_error' => $curlError,
                    'curl_errno' => $curlErrno
                ]);
                return [];
            }

            if ($httpCode !== 200) {
                \Log::warning('Yandex Suggest API вернул неожиданный HTTP код', [
                    'query' => $query,
                    'http_code' => $httpCode
                ]);
                return [];
            }

            $data = json_decode($response, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                \Log::error('Ошибка декодирования JSON от Yandex Suggest API', [
                    'query' => $query,
                    'json_error' => json_last_error_msg(),
                    'response_preview' => substr($response, 0, 500)
                ]);
                return [];
            }

            // Логируем структуру ответа для отладки
            \Log::info('Yandex Suggest API ответ', [
                'query' => $query,
                'response_keys' => is_array($data) ? array_keys($data) : 'не массив',
                'response_structure' => is_array($data) ? json_encode(array_slice($data, 0, 2, true)) : 'не массив'
            ]);

            // Обрабатываем ответ от Yandex Suggest API
            $suggestions = [];
            
            // Проверяем разные возможные структуры ответа
            $results = null;
            if (isset($data['results']) && is_array($data['results'])) {
                $results = $data['results'];
            } elseif (isset($data['suggestions']) && is_array($data['suggestions'])) {
                $results = $data['suggestions'];
            } elseif (isset($data['items']) && is_array($data['items'])) {
                $results = $data['items'];
            } elseif (isset($data['data']) && is_array($data['data'])) {
                $results = $data['data'];
            }
            
            // Если не нашли результаты, логируем полную структуру
            if (!$results) {
                \Log::warning('Не найдены результаты в ответе Yandex Suggest API', [
                    'query' => $query,
                    'full_response' => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
                ]);
            }
            
            if ($results && is_array($results)) {
                foreach ($results as $result) {
                    $title = null;
                    $subtitle = null;
                    $fullAddress = null;
                    
                    // Пытаемся извлечь title и subtitle из разных структур
                    if (isset($result['title']['text'])) {
                        $title = $result['title']['text'];
                    } elseif (isset($result['title'])) {
                        $title = is_string($result['title']) ? $result['title'] : ($result['title']['text'] ?? null);
                    } elseif (isset($result['value'])) {
                        $title = $result['value'];
                    } elseif (isset($result['text'])) {
                        $title = $result['text'];
                    } elseif (isset($result['display_name'])) {
                        $title = $result['display_name'];
                    } elseif (isset($result['name'])) {
                        $title = $result['name'];
                    }
                    
                    if (isset($result['subtitle']['text'])) {
                        $subtitle = $result['subtitle']['text'];
                    } elseif (isset($result['subtitle'])) {
                        $subtitle = is_string($result['subtitle']) ? $result['subtitle'] : ($result['subtitle']['text'] ?? null);
                    } elseif (isset($result['description'])) {
                        $subtitle = $result['description'];
                    } elseif (isset($result['description_text'])) {
                        $subtitle = $result['description_text'];
                    }
                    
                    // Пытаемся получить полный адрес напрямую
                    if (isset($result['address'])) {
                        if (is_string($result['address'])) {
                            $fullAddress = $result['address'];
                        } elseif (isset($result['address']['formatted'])) {
                            $fullAddress = $result['address']['formatted'];
                        } elseif (isset($result['address']['full'])) {
                            $fullAddress = $result['address']['full'];
                        }
                    }
                    
                    // Если есть полный адрес, используем его
                    if ($fullAddress) {
                        $suggestions[] = [
                            'value' => $fullAddress,
                            'display' => $fullAddress,
                            'title' => $title ?? $fullAddress,
                            'subtitle' => $subtitle ?? ''
                        ];
                    } elseif ($title) {
                        // Формируем полный адрес в формате: Город, Улица, дом
                        $displayAddress = $title;
                        if (!empty($subtitle) && $subtitle !== $title) {
                            // Если subtitle содержит город, объединяем их
                            // Проверяем, не содержится ли уже город в title
                            if (stripos($title, $subtitle) === false) {
                                $displayAddress = $subtitle . ', ' . $title;
                            } else {
                                $displayAddress = $title;
                            }
                        }

                        $suggestions[] = [
                            'value' => $displayAddress,
                            'display' => $displayAddress,
                            'title' => $title,
                            'subtitle' => $subtitle ?? ''
                        ];
                    }
                }
            }
            
            // Если получили результаты через Suggest API, сохраняем в кеш
            if (!empty($suggestions)) {
                Cache::put($cacheKey, $suggestions, now()->addDay());
            }
            
            \Log::info('Итоговые подсказки', [
                'query' => $query,
                'count' => count($suggestions),
                'suggestions' => array_map(function($s) { return $s['display']; }, $suggestions)
            ]);

            return $suggestions;
        } catch (\Exception $e) {
            \Log::error('Исключение при получении подсказок адресов', [
                'query' => $query,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [];
        }
    }
    
    /**
     * Получает подсказки через Geocoder API (основной метод)
     * 
     * @param string $query Поисковый запрос
     * @param int $maxResults Максимальное количество результатов
     * @return array Массив подсказок
     */
    private function getSuggestionsViaGeocoder(string $query, int $maxResults = 3): array
    {
        try {
            // Для подсказок используем оригинальный запрос без агрессивной нормализации
            // Это позволит находить адреса по части названия (например, "Сар" -> "Саратов")
            $searchQuery = trim($query);
            
            // Определяем, что уже введено пользователем
            $queryLower = mb_strtolower($searchQuery, 'UTF-8');
            $hasCity = preg_match('/\b(?:саратов|москва|санкт-петербург|петербург|спб)\b/ui', $queryLower);
            $hasStreet = preg_match('/\b(?:ул\.?|улица|проспект|пр\.?|пр-т|переулок|пер\.?|бульвар|бул\.?|имени|им\.?)\b/ui', $queryLower);
            $hasNumber = preg_match('/\d+/', $query);
            
            $params = [
                'apikey' => $this->getApiKey(),
                'geocode' => $searchQuery,
                'format' => 'json',
                'results' => $maxResults
            ];
            
            $url = 'https://geocode-maps.yandex.ru/v1/?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
            
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 3,
                CURLOPT_CONNECTTIMEOUT => 1,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_USERAGENT => 'Laravel Geocoder',
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($response === false || $httpCode !== 200) {
                \Log::warning('Geocoder API вернул ошибку при получении подсказок', [
                    'query' => $query,
                    'http_code' => $httpCode
                ]);
                return [];
            }
            
            $data = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                \Log::warning('Ошибка декодирования JSON от Geocoder API', [
                    'query' => $query,
                    'json_error' => json_last_error_msg()
                ]);
                return [];
            }
            
            $suggestions = [];
            
            if (isset($data['response']['GeoObjectCollection']['featureMember'])) {
                $members = array_slice($data['response']['GeoObjectCollection']['featureMember'], 0, $maxResults * 2); // Берем больше для фильтрации
                
                foreach ($members as $member) {
                    if (isset($member['GeoObject'])) {
                        $geoObject = $member['GeoObject'];
                        $name = $geoObject['name'] ?? '';
                        $description = $geoObject['description'] ?? '';
                        
                        // Формируем адрес в формате: Город, улица, дом
                        $displayAddress = $name;
                        if (!empty($description) && $description !== $name) {
                            // Если description содержит более полную информацию, используем её
                            // Проверяем, не содержится ли уже описание в названии
                            if (stripos($name, $description) === false && stripos($description, $name) === false) {
                                // Объединяем: обычно description - это город, name - это улица и дом
                                $displayAddress = $description . ', ' . $name;
                            } else {
                                $displayAddress = $name;
                            }
                        }
                        
                        if (!empty($displayAddress)) {
                            // Фильтруем результаты по уже введенному тексту
                            $displayLower = mb_strtolower($displayAddress, 'UTF-8');
                            
                            // Если пользователь уже ввел улицу, не показываем только город
                            if ($hasStreet && !$hasNumber) {
                                // Пользователь ввел улицу, показываем только адреса с улицей и номером
                                $displayHasStreet = preg_match('/\b(?:ул\.?|улица|проспект|пр\.?|пр-т|переулок|пер\.?|бульвар|бул\.?|имени|им\.?)\b/ui', $displayLower);
                                $displayHasNumber = preg_match('/\d+/', $displayAddress);
                                if (!$displayHasStreet || !$displayHasNumber) {
                                    continue; // Пропускаем этот результат
                                }
                            }
                            
                            // Если пользователь уже ввел город и улицу, показываем только адреса с номером
                            if ($hasCity && $hasStreet && !$hasNumber) {
                                $displayHasNumber = preg_match('/\d+/', $displayAddress);
                                if (!$displayHasNumber) {
                                    continue; // Пропускаем этот результат
                                }
                            }
                            
                            // Проверяем, что результат содержит введенный текст
                            $queryWords = preg_split('/\s+/', $queryLower);
                            $allWordsMatch = true;
                            foreach ($queryWords as $word) {
                                if (mb_strlen($word) > 2 && mb_stripos($displayLower, $word) === false) {
                                    $allWordsMatch = false;
                                    break;
                                }
                            }
                            
                            if (!$allWordsMatch && mb_strlen($query) > 5) {
                                continue; // Пропускаем, если не все слова совпадают (только для длинных запросов)
                            }
                            
                            $suggestions[] = [
                                'value' => $displayAddress,
                                'display' => $displayAddress,
                                'title' => $name,
                                'subtitle' => $description
                            ];
                            
                            // Ограничиваем количество результатов
                            if (count($suggestions) >= $maxResults) {
                                break;
                            }
                        }
                    }
                }
            }
            
            \Log::info('Подсказки получены через Geocoder API', [
                'query' => $query,
                'count' => count($suggestions)
            ]);
            
            return $suggestions;
        } catch (\Exception $e) {
            \Log::error('Ошибка при получении подсказок через Geocoder API', [
                'query' => $query,
                'exception' => $e->getMessage()
            ]);
            return [];
        }
    }
}