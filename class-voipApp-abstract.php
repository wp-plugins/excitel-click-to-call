<?php
class VoipApp_Abstract{

    const VOIP_IP = 'http://stage.excitel.ru';
    const API_VERSION = '1';

    public function voipApp_curl_init($url,$params,$method = 'POST'){
        $request_host   = 'stage.excitel.ru';
        $request_url    = self::VOIP_IP;
        $headers = array("Host: ".$request_host,"Accept: application/vnd.example.api+json;version=".self::API_VERSION);
        $curl=curl_init();
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($curl,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/535.6 (KHTML, like Gecko) Chrome/16.0.897.0 Safari/535.6');
        curl_setopt($curl,CURLOPT_URL, $request_url . $url);
        curl_setopt($curl,CURLOPT_CUSTOMREQUEST,$method);
        curl_setopt($curl,CURLOPT_POSTFIELDS,json_encode($params));
        curl_setopt($curl,CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl,CURLOPT_HEADER,false);
        curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,0);
        curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,0);
        //TODO: delete stage auth
        curl_setopt($curl, CURLOPT_USERPWD, "stage:zxc123");

        $out = curl_exec($curl);
        $code = curl_getinfo($curl,CURLINFO_HTTP_CODE); #Получим HTTP-код ответа сервера

        curl_close($curl); #Заверашем сеанс cURL

        $code=(int)$code;
        return array(
            'body' => json_decode($out),
            'code' => $code);
    }
}