<?php

namespace requests {

    use Exception;
    use data\invoice_payload;

    abstract class request_component
    {
        abstract function componentResult();
    }

    abstract class request
    {
        private $header_array;
        private $content;
        private $type;

        /**
         * request constructor.
         * @param $type int as declared in request_type
         * @param $header_array array of strings that represent headers (optional)
         * @param $content string of contents (optional)
         */
        public function __construct($type, $header_array = null, $content = null)
        {
            $this->type = $type;
            $this->header_array = $header_array;
            $this->content = $content;
        }

        /**
         * @return string of the request type
         */
        public function getType()
        {
            return $this->type;
        }


        /**
         * @return array of strings representing header fields
         */
        public function getHeaderArray()
        {
            return $this->header_array;
        }

        /**
         * @return string represents HTTP content
         */
        public function getContent()
        {
            return $this->content;
        }

        abstract function getContext();
    }

    class auth_type
    {
        const API_KEY = "apiKey";
        const BASIC = "basic";
    }

    class request_type {
        const GET = 0;
        const POST = 1;
        const PUT = 2;
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


    class sale_invoice_create_request extends request
    {
        /**
         * Default constructor
         * @param $invoice_data invoice_payload
         * @throws Exception when required fields are missing
         */
        public function __construct($invoice_data)
        {
            // if (!$invoice_data->validate())
            //   throw new Exception("Invalid data supplied");
            parent::__construct(request_type::POST,
                ["Content-Type: application/json"],
                json_encode($invoice_data->getData())
            );
        }

        function getContext()
        {
            return "sales/invoices";
        }
    }


}
