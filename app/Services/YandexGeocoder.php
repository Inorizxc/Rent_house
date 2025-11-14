<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class YandexGeocoder{
    private string $apiKey;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey = "a2cd05de-c1e4-457b-8092-a8b0ebd9db10";
    }

    public function getCoordinates(string $address): ?array
    {
        $address = urlencode($address);
        $url = 'https://geocode-maps.yandex.ru/v1/?apikey=a2cd05de-c1e4-457b-8092-a8b0ebd9db10&geocode='.$address."&format=json";
        
        $ch = curl_init();



        echo $url;
        $url = preg_replace("/ /", "%20", $url);
        
        $response = file_get_contents($url);
        $data = json_decode($response, true);
        $obj = $data['response']['GeoObjectCollection'];
        //list($longitude, $latitude) = explode(' ', $obj['Point']['pos']);
        return $obj;
    }
}