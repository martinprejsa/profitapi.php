<?php

namespace profitapi {
    use Exception;

    function isJson($string) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    class communicator
    {
        const API_VERSION = "1.6";
        const URL = "https://api.profit365.eu";

        private $auth_header;
        public $lastResult;

        /**
         * Default constructor.
         * @param $auth_header auth_header
         */
        public function __construct($auth_header)
        {
            $this->auth_header = $auth_header;
        }

        /**
         * Used to communicate with api. Result of this action can be found at communicator.lastResult
         * @param $request request Request to be sent timeout
         * @return integer HTTP response code
         */
        public function request($request)
        {
            if($request->getHeaderArray() == null)
                $headers = $this->auth_header->componentResult();
            else $headers = array_merge($this->auth_header->componentResult(), $request->getHeaderArray());

            $url = self::URL . "/" . self::API_VERSION . "/" . $request->getContext();
            $con = curl_init($url);
            $content = $request->getContent();

            if($request->getType() == request_type::POST) {
                curl_setopt($con, CURLOPT_POST, true);
                curl_setopt($con, CURLOPT_POSTFIELDS, $content);
            } else if($request->getType() == request_type::PUT) {
                curl_setopt($con, CURLOPT_PUT, true);
                curl_setopt($con, CURLOPT_POSTFIELDS, $content);
            }

            curl_setopt($con, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($con, CURLOPT_HTTPHEADER, $headers);

            $response = curl_exec($con);
            $code = curl_getinfo($con, CURLINFO_RESPONSE_CODE);

            curl_close($con);

            if(isJson($response))
                $this->lastResult =  new payload(json_decode($response));
            else
                $this->lastResult = $response;

            return $code;
        }
    }

    class auth_type
    {
        const API_KEY = 0;
        const BASIC = 1;

        static function val($type) {
            if($type == self::API_KEY)
                return "apiKey";
            if($type == self::BASIC)
                return "basic";
        }
    }

    class auth_header extends request_component
    {
        const BASIC_AUTH_PATTERN = "/(.*.)(\@)(.*)(\...*):(..*)/";
        private $type;
        private $auth_key;
        private $client_secret;
        private $client_id;
        private $company_id;

        /**
         * auth_header constructor.
         * @param $auth_type int
         * @param $auth_key string
         * @param $client_secret string
         * @param $client_id string
         * @param $company_id string default null
         * @param $base64_key bool default false, set to true if auth_key parameter is in base64 already
         *
         * @throws Exception when invalid non base64 auth_key is passed or auth_type is invalid
         */
        public function __construct($auth_type, $auth_key, $client_secret, $client_id, $company_id = null, $base64_key = false)
        {
            $this->type = $auth_type;
            if ($auth_type == auth_type::BASIC) {
                if (!$base64_key && preg_match(self::BASIC_AUTH_PATTERN, $auth_key) != 1)
                    throw new Exception("Basic authorization requires valid auth key");
                $this->auth_key = $base64_key ? $auth_key : base64_encode($auth_key);
            } else if ($auth_type == $auth_type::API_KEY)
                $this->auth_key = $auth_key;
            else
                throw new Exception("Invalid auth type");

            $this->client_secret = $client_secret;
            $this->client_id = $client_id;
            $this->company_id = $company_id;
        }

        function generateApiKey() {
            //TODO
        }

        function componentResult()
        {
            $headers = array(
                "ClientID: $this->client_id",
                "ClientSecret: $this->client_secret",
                "Authorization: ". auth_type::val($this->type)." $this->auth_key",
            );
            if ($this->company_id != null)
                array_push($headers, "CompanyID: $this->company_id");
            return $headers;
        }
    }

}