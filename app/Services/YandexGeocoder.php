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
     * Формат: убирает пробелы, запятые, слова типа "улица", "дом" и другие разделители
     * Оставляет только буквы и цифры. Пример: "Саратов, улица Исаева, 5" -> "СаратовИсаева5"
     * 
     * @param string $address Адрес для нормализации (например: "Саратов,Исаева,3" или "Саратов, улица Исаева, 5")
     * @return string Нормализованный адрес
     */
    private function normalizeAddress(string $address): string
    {
        // Убираем лишние пробелы
        $address = preg_replace('/\s+/', ' ', trim($address));
        
        // Заменяем запятые на пробелы для лучшей обработки
        $address = str_replace(',', ' ', $address);
        
        // Убираем слова типа "улица", "проспект", "дом" и их сокращения
        // Делаем это ДО удаления пробелов, чтобы правильно находить границы слов
        $address = preg_replace('/\b(?:ул\.?|улица|проспект|пр\.?|пр-т|переулок|пер\.?|бульвар|бул\.?|дом|д\.?)\s*/ui', '', $address);
        
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
        
        // Нормализуем адрес перед обработкой
        $normalizedAddress = $this->normalizeAddress($address);
        
        // Проверяем кеш перед запросом к API
        $cacheKey = 'geocoder_' . md5($normalizedAddress);
        try {
            $cachedResult = Cache::get($cacheKey);
            
            if ($cachedResult !== null && is_array($cachedResult) && isset($cachedResult['lat']) && isset($cachedResult['lng'])) {
                \Log::info('Координаты получены из кеша', [
                    'original_address' => $originalAddress,
                    'normalized_address' => $normalizedAddress,
                    'cache_key' => $cacheKey
                ]);
                return $cachedResult;
            }
        } catch (\Exception $e) {
            \Log::warning('Ошибка при чтении кеша, продолжаем запрос к API', [
                'original_address' => $originalAddress,
                'normalized_address' => $normalizedAddress,
                'error' => $e->getMessage()
            ]);
        }
        
        // Используем http_build_query для правильного кодирования параметров
        // PHP_QUERY_RFC3986 использует rawurlencode, который правильно обрабатывает кириллицу
        $params = [
            'apikey' => $this->getApiKey(),
            'geocode' => $normalizedAddress, // http_build_query правильно закодирует кириллицу
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
                    'normalized_address' => $normalizedAddress,
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
                        'normalized_address' => $normalizedAddress,
                        'url' => str_replace($this->getApiKey(), '***', $url),
                        'curl_error' => $curlError,
                        'curl_errno' => $curlErrno
                    ]);
                } else {
                \Log::error('Ошибка при запросе к Yandex Geocoder API', [
                        'original_address' => $originalAddress,
                        'normalized_address' => $normalizedAddress,
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
                    'normalized_address' => $normalizedAddress,
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
                    'normalized_address' => $normalizedAddress,
                    'url' => str_replace($this->getApiKey(), '***', $url),
                    'response_preview' => substr($response, 0, 500)
                ]);
                // Попробуем распарсить XML как fallback
                $coordinates = $this->parseXmlResponse($response, $originalAddress);
                if ($coordinates) {
                    // Сохраняем результат в кеш
                    Cache::put($cacheKey, $coordinates, now()->addDays(30));
                }
                return $coordinates;
            }
            
            $data = json_decode($response, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                \Log::error('Ошибка декодирования JSON от Yandex Geocoder', [
                    'original_address' => $originalAddress,
                    'normalized_address' => $normalizedAddress,
                    'json_error' => json_last_error_msg(),
                    'response_preview' => substr($response, 0, 500)
                ]);
                return null;
            }
            
            // Проверяем наличие ошибок в ответе API
            if (isset($data['error'])) {
                \Log::error('Yandex Geocoder вернул ошибку', [
                    'original_address' => $originalAddress,
                    'normalized_address' => $normalizedAddress,
                    'error' => $data['error'],
                    'full_response' => $response
                ]);
                return null;
            }
            
            if (!isset($data['response'])) {
                \Log::warning('Неожиданный формат ответа от Yandex Geocoder', [
                    'original_address' => $originalAddress,
                    'normalized_address' => $normalizedAddress,
                    'response_keys' => isset($data) && is_array($data) ? array_keys($data) : 'не массив',
                    'response_preview' => is_array($data) ? json_encode(array_slice($data, 0, 3, true)) : substr($response, 0, 500)
                ]);
                return null;
            }
            
            // Проверяем наличие GeoObjectCollection
            if (!isset($data['response']['GeoObjectCollection'])) {
                \Log::warning('Ответ не содержит GeoObjectCollection', [
                    'original_address' => $originalAddress,
                    'normalized_address' => $normalizedAddress,
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
                    'normalized_address' => $normalizedAddress,
                    'found' => $found,
                    'metaDataProperty' => $metaData
                ]);
                return null;
            }
            
            $geoObject = $collection['featureMember'][0]['GeoObject'];
            
            if (!isset($geoObject['Point']['pos'])) {
                \Log::warning('Геообъект не содержит координат', [
                    'original_address' => $originalAddress,
                    'normalized_address' => $normalizedAddress,
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
                    'normalized_address' => $normalizedAddress,
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
            Cache::put($cacheKey, $coordinates, now()->addDays(30));
            
            \Log::info('Координаты успешно получены и сохранены в кеш', [
                'original_address' => $originalAddress,
                'normalized_address' => $normalizedAddress,
                'coordinates' => $coordinates
            ]);
            
            return $coordinates;
        } catch (\Exception $e) {
            \Log::error('Исключение при получении координат', [
                'original_address' => $originalAddress,
                'normalized_address' => $normalizedAddress ?? null,
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
}