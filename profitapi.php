<?php

namespace profitapi {

    include_once "profitapi_data.php";
    include_once "profitapi_requests.php";

    use Exception;
    use requests\request;

    class communicator
    {
        const API_VERSION = "1.4";
        const URL = "https://api.profit365.eu";

        private $auth_header;

        /**
         * Default constructor.
         * @param $auth_header auth_header
         */
        public function __construct($auth_header)
        {
            $this->auth_header = $auth_header;
        }

        /**
         * Used to communicate with api.
         * @param $request request Request to be sent timeout
         * @return boolean TRUE on success FALSE otherwise
         */
        public function request($request, $type)
        {
            if($request->getHeaderArray() == null)
                $headers = $this->auth_header->componentResult();
            else $headers = array_merge($this->auth_header->componentResult(), $request->getHeaderArray());

            $url = self::URL . "/" . self::API_VERSION . "/" . $request->getContext();
            $con = curl_init($url);

            if($type == request_type::POST_JSON) {
                $content = $request->getContent();
                array_push($headers, "Content-Type: application/json");
                curl_setopt($con, CURLOPT_POST, true);
                curl_setopt($con, CURLOPT_POSTFIELDS, $content);
            }

            curl_setopt($con, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($con, CURLOPT_HTTPHEADER, $headers);


            $response = curl_exec($con);
            echo curl_getinfo($con, CURLINFO_HTTP_CODE) . "<br>";
            echo $url;
            curl_close($con);

            return $response;
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
         * @param $type string
         * @param $auth_key string
         * @param $client_secret string
         * @param $client_guid string
         * @param null $company_id string
         * @throws Exception when authorization key doesnt match pattern when using BASIC auth
         */
        public function __construct($type, $auth_key, $client_secret, $client_guid, $company_id = null)
        {
            $this->type = $type;
            if ($type == auth_type::BASIC) {
                if (preg_match(self::BASIC_AUTH_PATTERN, $auth_key) != 1)
                    throw new Exception("basic authorization requires valid auth key");
                $this->auth_key = base64_encode($auth_key);
            } else
                $this->auth_key = $auth_key;

            $this->client_secret = $client_secret;
            $this->client_id = $client_guid;
            $this->company_id = $company_id;
        }

        function componentResult()
        {
            $headers = array(
                "ClientID: $this->client_id",
                "ClientSecret: $this->client_secret",
                "Authorization: $this->type $this->auth_key",
            );
            if ($this->company_id != null)
                array_push($headers, "CompanyID: $this->company_id");
            return $headers;
        }
    }

    abstract class request_component
    {
        abstract function componentResult();
    }

    class auth_type
    {
        const API_KEY = "apiKey";
        const BASIC = "basic";
    }
    class request_type {
        const POST_JSON = "postjson";
        const POST_XML = "postxml";
        const GET = "get";
    }

}



