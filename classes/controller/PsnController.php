<?php

namespace classes\controller;
use packagetest\PackageTest;
use restphp\http\RestHttpRequest;
use restphp\http\RestHttpResponse;
use curltools\CurlTools;
#RequestMapping(value="/psn")
class PsnController {
    private $clientAuthUrl = "https://ca.account.sony.com/api/";
    private $restClientBase = "https://m.np.playstation.net/api/";
    
#RequestMapping(value="/", method="GET")
    public function Index(){
        RestHttpResponse::redirect("../doc/psn/index.html");
    }

    /**
     * 获取token
     */
    #RequestMapping(value="/token", method="GET")
    public function ReflushToken() {

        $auth_key=RestHttpRequest::getGet("auth_key");
        $psn_npsso = RestHttpRequest::getGet("npsso");
        $client_id = RestHttpRequest::getGet("client_id");

        if($auth_key == null  || $psn_npsso == null || $client_id == null){

            // RestHttpResponse::jsonErr(array(
            RestHttpResponse::json(array(
                "code"=>400,
                "access_token"=>"",
                "expires"=>-1,
                "error"=>"Bad request",
                "error_description"=>"Required field not found",
            ));
            die();
        }

        $this->CheckAuthKey($auth_key);

        $code = $this->GetClientCode($psn_npsso,$client_id);

        $requestPostBody = array(
            "grant_type"=>"authorization_code",
            "redirect_uri"=> "com.playstation.PlayStationApp://redirect",
            "code"=>$code
        );
        
        $requsetUrl = $this->clientAuthUrl . "authz/v3/oauth/token";

        $defaultHeader = $this->GetDefaultHeader($psn_npsso);

        $content = CurlTools::Http_Post($requsetUrl,$defaultHeader,$requestPostBody);

        if($content == null || $content == ""){
            RestHttpResponse::json(array(
                "code"=>500,
                "access_token"=>"",
                "expires"=>-1,
                "error"=>"req error",
                "error_description"=>"req token error,response null",
            ));
            die();
        }
        $result = null;
        try {
            $result = json_decode($content);

            if(isset($result->error)){
                RestHttpResponse::json(array(
                    "code"=> $result->error_code ?? 503,
                    "access_token"=>"",
                    "expires"=>-1,
                    "error"=>$result->error,
                    "error_description"=>$result->error_description ?? "psn server error",
                ));
                die();
            }

            RestHttpResponse::json(array(
                "code"=> 200,
                "access_token"=>$result->access_token ?? "",
                "expires"=>$result->expires_in ?? -1,
                "error"=>"",
                "error_description"=>"",
            ));
            die();

        } catch (\Throwable $th) {
            //throw $th;
            if($content == null || $content == ""){
                RestHttpResponse::json(array(
                    "code"=>503,
                    "access_token"=>"",
                    "expires"=>-1,
                    "error"=>"decode res error",
                    "error_description"=>"res token decode error",
                ));
                die();
            }
        }
        

        //没有 bear
        /* 401
        {"error":"invalid_client","error_description":"The client was not found","error_code":4161,"error_uri":"https://auth.api.sonyentertainmentnetwork.com/openapi/docs"}
        */
        /**
         * code 没写（改完密码获取不到）
         * {"error":"invalid_request","error_description":"Mandatory parameter 'code' is missing","error_code":4098,"error_uri":"https://auth.api.sonyentertainmentnetwork.com/openapi/docs"}
         */
        /**
         * code 写了，但是不对
         * {\"error\":\"invalid_grant\",\"error_description\":\"Invalid authorization code\",\"error_code\":4650,\"error_uri\":\"https:\/\/auth.api.sonyentertainmentnetwork.com\/openapi\/docs\"}
         */
    }

    /**
     * 获取当前的 code 
     */
    #RequestMapping(value="/code", method="GET")
    public function GetVersionCode(){

        $auth_key=RestHttpRequest::getGet("auth_key");
        $psn_npsso = RestHttpRequest::getGet("npsso");
        $client_id = RestHttpRequest::getGet("client_id");

        if($auth_key == null  || $psn_npsso == null || $client_id == null){
            //TODO: error
            RestHttpResponse::json(array(
                "code"=>400,
                "access_token"=>"",
                "expires"=>-1,
                "error"=>"Bad request",
                "error_description"=>"Required field not found",
            ));
            die();
        }


        $this->CheckAuthKey($auth_key);
        $client_code = $this->GetClientCode($psn_npsso,$client_id);
        RestHttpResponse::json(array(
            "code"=>200,
            "client_code"=> $client_code,
            "expires"=>-1,
            "error"=>"",
            "error_description"=>"",
        ));
    }

    /**
     * 获取游戏列表
     */
    #RequestMapping(value="/gamelist", method="GET")
    public function GetGameList(){
        $auth_key=RestHttpRequest::getGet("auth_key");
        $psn_npsso = RestHttpRequest::getGet("npsso");
        $client_id = RestHttpRequest::getGet("client_id");
        $account_id = RestHttpRequest::getGet("account_id");
        $token=RestHttpRequest::getGet("token");
        $offset= \intval(RestHttpRequest::getGet("offset") ?? 0);
        $limit= \intval(RestHttpRequest::getGet("limit")?? 10);
        $categories=RestHttpRequest::getGet("categories") ?? "ps4_game,ps5_native_game";
        if($auth_key == null  || $psn_npsso == null || $client_id == null || $account_id== null){

            RestHttpResponse::json(array(
                "code"=>400,
                "data"=> null,
                "error"=>"Bad request",
                "error_description"=>"Required field not found",
            ));
            die();
        }
        $this->CheckAuthKey($auth_key);

        $client_code = $this->GetClientCode($psn_npsso,$client_id);

        $getParam = array(
            "offset"=> $offset,
            "limit"=> $limit,
            "categories"=>$categories
        );
        $header = array(
            "Authorization"=> "Authorization:Bearer " . $token,
        );
        $fullGetUrl = $this->restClientBase . "gamelist/v2/users/" . $account_id . "/titles?" . http_build_query($getParam);
        
        $content = CurlTools::Http_Get($fullGetUrl,$header);



        if($content == null || $content == ""){
            RestHttpResponse::json(array(
                "code"=>500,
                "data"=>null,
                "error"=>"req error",
                "error_description"=>"req gamelist error,response null",
            ));
            die();
        }
        $result = null;
        try {
            $result = json_decode($content);

            if(isset($result->error)){
                RestHttpResponse::json(array(
                    "code"=> $result->error->code ?? 503,
                    "data"=> $content,
                    "error"=>$result->error->reason ?? "Get gamelist failed",
                    "error_description"=>$result->error->message ?? "Get gamelist failed",
                ));
                die();
            }

            RestHttpResponse::json(array(
                "code"=> 200,
                "data"=> $result,
                "error"=>"",
                "error_description"=>"",
            ));
            die();

        } catch (\Throwable $th) {
            if($content == null || $content == ""){
                RestHttpResponse::json(array(
                    "code"=>503,
                    "data"=> $content,
                    "error"=>"decode res error",
                    "error_description"=>"res gamelist decode error",
                ));
                die();
            }
        }

    }

    /**
     * 获取 NPSSO
     */
    #RequestMapping(value="/npsso", method="GET")
    public function GetNpsso(){
        RestHttpResponse::redirect("https://ca.account.sony.com/api/v1/ssocookie#/signin/ca?entry=ca");
    }

    /**
     * 获取认证码
     */
    #RequestMapping(value="/authkey", method="GET")
    public function GetAuthContent(){


        RestHttpResponse::html("what are you finding ?");
    }

    private function GetDefaultHeader($psn_npsso){
        return array(
            "User-Agent"=>"User-Agent:Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0",
            "Authorization"=> "Authorization:Basic YWM4ZDE2MWEtZDk2Ni00NzI4LWIwZWEtZmZlYzIyZjY5ZWRjOkRFaXhFcVhYQ2RYZHdqMHY=",
            "Cookie"=>"Cookie:npsso=" . $psn_npsso
        );
    }

    private function GetClientCode($psn_npsso,$client_id){
        $authArray = array(
            "client_id"=>$client_id,
            "redirect_uri"=> "com.playstation.PlayStationApp://redirect",
            "response_type" => "code",
            "scope" => "psn:mobile.v1 psn:clientapp"
        );
        
        $header = $this->GetDefaultHeader($psn_npsso);

        $getParam = http_build_query($authArray);
        $getClientCodeUrl = $this->clientAuthUrl . "authz/v3/oauth/authorize?" . $getParam;
        // return $getClientCodeUrl;
        $allHeader = CurlTools::Http_GetHeader($getClientCodeUrl,$header);



        //错误的情况
        //改完密码：
        //https://my.account.sony.com/central/signin/?
        //client_id=xxxxxxxxxxxxxxxxxxxxxxxx&
        //redirect_uri=com.playstation.PlayStationApp%3A%2F%2Fredirect&
        //response_type=code
        //&scope=psn%3Amobile.v1+psn%3Aclientapp
        //&auth_ver=v3
        //&error=login_required
        //&error_code=4165
        //&error_description=User+is+not+authenticated
        //&no_captcha=false
        //&cid=zzzzzzzzzzzzzzzzzzzzzzzz

        //没有clientid：
        // 400 错误 Bad request

        if($allHeader['code'] == 400){
            return "v3.badrequest";
        }
        try {
            if(isset($allHeader["Location"])){
                //com.playstation.PlayStationApp://redirect/?code=xxxxxxxxxxxxxxxxxxxxx
                $code = $this->get_match_all("/\?code=(.+?)\&/",$allHeader["Location"]);
                return $code;
            }
        } catch (\Throwable $th) {

        }
       
        return "v3.9AAAAA";
    }

    private function CheckAuthKey($auth_key){
        try{
            $patht = dirname(dirname(dirname(dirname(__FILE__))));

            $all = file_get_contents($patht . "/authkey.json");

            $allcode =json_decode($all);

            $v = \sizeof($allcode);

            for ($i=0; $i < $v; $i++) { 
                if(  \strval($auth_key) === $allcode[$i]){
                    return;
                }
            }

        }catch(\Throwable $th){
            //JUST RETURN,need modify
            // return;
            error_log($th);
        }

        RestHttpResponse::json(array(
            "code"=>401,
            "access_token"=>"",
            "expires"=>-1,
            "error"=>"Authentication failed",
            "error_description"=>"Authentication failed,please contact admin",
        ));
        die();
    }

    function get_match_all($reg,$str){
        preg_match_all($reg,$str,$r);
        return @$r[1][0];
    }
}