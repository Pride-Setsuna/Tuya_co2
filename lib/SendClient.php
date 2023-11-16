<?php

class SendClient{

    public $accessUrl = 'https://openapi.tuyaus.com';
    //Access ID / Access Secret
    public $clientID = 'x95g4f5ysutefhy3qehv';
    public $secret = '0e905a81cd8c4dbf83cc038464a8d3fb';

    public $tstamp = 0;

    public static $AccessToken = '';

    //UUID, add if you want
    public $nonce = '';


    /** construct
     * @param array $param config
     */
    public function __construct( $param =  [] ){
        set_time_limit (0);
        //ini_set('memory_limit','128M');
        $config = ['clientID','secret','accessUrl','nonce'];
        $this->tstamp = self::getMillisecond();

        foreach ($param as $key=>$value){
            if( in_array($key,$config) && isset($value) ){
                $this->$key = $value;
            }
        }
        //get token
        $this->getToken();
    }

    /** sync user
     * @param string $schema
     * @param string $body
     * @param array $header
     * @return array
     */
    public function syncUser($schema='',$body='',$extra_header=[]){

        $uri = "/v1.0/apps/$schema/user";

        $content = hash("sha256",$body);
        $signHeader = self::buildHeader($extra_header);

        $stringToSign = self::strToSign('POST',$content,$signHeader,$uri);

        $str = $this->clientID.self::$AccessToken.$this->tstamp.$this->nonce.$stringToSign;

        $headers = [
            'client_id'     => $this->clientID,
            'sign'          => self::hashSign($str),
            'method'        => 'POST',
            't'             => $this->tstamp,
            'access_token'  => self::$AccessToken,
            'sign_method'   => 'HMAC-SHA256',
            //'lang'        => 'zh',
        ];
        $headers = self::buildRequestHeader($headers,$extra_header);

        $result = self::curlRequest($uri,'POST',$headers,$body);
        if($result['success']==true && isset($result['result'])){
            $info = $result['result'];
            return $this->alertMsg(1,'success', $info['uid']);
        }else{
            return $this->alertMsg(-1,$result['msg']);
        }
    }

    public function device($schema='',$body='',$extra_header=[]){

        $uri = "/v1.0/devices?device_ids=ebd9fe9f61070ed7f9pvip,ebe8e112beca4f6377qtkw,ebdb52996997df7308f8p4,eb7f8df07e050df845ruks,eb6dc8b12a45f277f4tbsq,ebe55c111bc004cd8bcpjt,ebb4568537a900714ezrnp&page_no=1&page_size=20";

        $content = hash("sha256",$body);
        $signHeader = self::buildHeader($extra_header);

        $stringToSign = self::strToSign('GET',$content,$signHeader,$uri);

        $str = $this->clientID.self::$AccessToken.$this->tstamp.$this->nonce.$stringToSign;

        $headers = [
            'client_id'     => $this->clientID,
            'sign'          => self::hashSign($str),
            'method'        => 'GET',
            't'             => $this->tstamp,
            'access_token'  => self::$AccessToken,
            'sign_method'   => 'HMAC-SHA256',
        ];
        $headers = self::buildRequestHeader($headers,$extra_header);

        return self::curlRequest($uri,'GET',$headers,$body);
        
    }

    /** Get user list
     * @param string $schema
     * @param array $condition
     * @param array $extra_header
     * @return array
     */
    public function getUserList($schema='',$condition=[],$extra_header=[]){

        $uri = "/v2.0/apps/$schema/users";
        $uri = self::buildUri($uri,$condition);

        $body = '';
        $content = hash("sha256",$body);
        $signHeader = self::buildHeader($extra_header);

        $stringToSign = self::strToSign('GET',$content,$signHeader,$uri);

        $str = $this->clientID.self::$AccessToken.$this->tstamp.$this->nonce.$stringToSign;

        $headers = [
            'client_id'     => $this->clientID,
            'sign'          => self::hashSign($str),
            'method'        => 'GET',
            't'             => $this->tstamp,
            'access_token'  => self::$AccessToken,
            'sign_method'   => 'HMAC-SHA256',
            //'lang'        => 'zh',
        ];
        $headers = self::buildRequestHeader($headers,$extra_header);

        $result = self::curlRequest($uri,'GET',$headers);
        if($result['success']==true && isset($result['result'])){
            $info = $result['result'];
            return $this->alertMsg(1,'success', $info);
        }else{
            return $this->alertMsg(-1,$result['msg']);
        }
    }

    /** get access_token & refresh_token
     * @return array
     */
    protected function getToken(){
        $uri = '/v1.0/token?grant_type=1';

        $body = '';
        $header = [];

        $content = hash("sha256",$body);
        $sigHeader = self::buildHeader($header);

        $stringToSign = self::strToSign('GET',$content,$sigHeader,$uri);

        $str = $this->clientID.$this->tstamp.$this->nonce.$stringToSign;


        $headers = [
            'client_id'     => $this->clientID,
            'sign'          => self::hashSign($str),
            'method'        => 'GET',
            't'             => $this->tstamp,
            'sign_method'   => 'HMAC-SHA256',
            //'lang'        => 'zh',
        ];

        $result = self::curlRequest($uri,'GET',$headers);
        if($result['success']==true && isset($result['result'])){
            self::$AccessToken = $result['result']['access_token'];
            return $this->alertMsg(1,'token acquired');
        }else{
            return $this->alertMsg(-1,$result['msg']);
        }
    }


    /** Generate stringToSign
     * @param $HTTPMethod
     * @param $Content_SHA256
     * @param $Headers
     * @param $url
     * @return string
     */
    private function strToSign($HTTPMethod,$Content_SHA256,$Headers,$url){
        /**
         * String stringToSign=
         *   HTTPMethod + "\n" +
         *   Content-SHA256 + "\n" +
         *   Headers + "\n" +
         *   Url
         */
        return $HTTPMethod."\n".$Content_SHA256."\n".$Headers."\n".$url;
    }


    /** HMAC SHA256
     * @param string $message
     * @return string
     */
    private function hashSign($message=''){
        return strtoupper(hash_hmac("sha256", $message, $this->secret));
    }

    /** Generate standard timestamp
     * @return float timestamp
     */
    private function getMillisecond() {
        list($t1, $t2) = explode(' ', microtime());
        return (float)sprintf('%.0f',(floatval($t1)+floatval($t2))*1000);
    }

    /** Build uri
     * @param string $uri
     * @param array $condition
     * @return string
     */
    private function buildUri($uri='',$condition=[]){
        if(!$condition)return $uri;
        $uri .= '?';$tArr = [];
        foreach($condition as $key=>$value){
            $tArr[]= $key.'='.$value;
        }
        return $uri.implode('&',$tArr);
    }

    /** Build header for strToSign() and curlRequest()
     * @param string $headers
     * @param bool $isArr is array
     * @return array|string
     */
    private function buildHeader($headers=[],$isArr=false){
        if(!$headers)return $isArr?[]:'';
        $newHeader = [];
        foreach($headers as $key=>$value){
            $newHeader[]=$key.':'.$value.($isArr?'':"\n");
        }
        return $isArr?$newHeader:implode("",$newHeader);
    }

    /** Build request header
     * @param array $headers
     * @param array $extra_headers
     */
    private function buildRequestHeader($headers=[],$extra_headers=[]){
        if($extra_headers){
            $keyArr = [];
            foreach ($extra_headers as $key => $value){
                $headers[$key] = $value;
                $keyArr[] = $key;
            }
            $headers['Signature-Headers']= implode(':',$keyArr);
        };
        return $headers;
    }

    /** Request
     * @param string $uri
     * @param string $method
     * @param array $headers
     * @param array $body
     * @return bool|string
     */
    private function curlRequest($uri='',$method='GET',$headers=[],$body=[]){
        $ch = curl_init();

        /* source
         * $host = isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '');
         * $ip = isset($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '');
         * curl_setopt($ch, CURLOPT_REFERER, $host.$ip);
         */
        curl_setopt($ch, CURLOPT_URL, $this->accessUrl.$uri);

        if(strtoupper($method)=='POST'){
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);

            $headers = array_merge(['Content-Type:application/json'],self::buildHeader($headers,true));
        }else{
            $headers = self::buildHeader($headers,true);
        }
        //return filestream
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        //skip ssl
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $output = curl_exec($ch);

        curl_close($ch);
        $result = @json_decode($output,true);
        return $result;
    }


    /** Return request info
     * @param int $code
     * @param string $message
     * @param mixed $data
     * @return array
     */
    protected function alertMsg($code=-1,$message='',$data=''){
        return array("code"=>$code,"message"=>$message,"data"=>$data);
    }

}
?>

