<?php

namespace profitapi {
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
        public function request($request)
        {
            if($request->getHeaderArray() == null)
                $headers = $this->auth_header->componentResult();
            else $headers = array_merge($this->auth_header->componentResult(), $request->getHeaderArray());

            $url = self::URL . "/" . self::API_VERSION . "/" . $request->getContext();
            $con = curl_init($url);

            if($request->getType() == request_type::POST_JSON) {
                $content = $request->getContent();
                array_push($headers, "Content-Type: application/json");
                curl_setopt($con, CURLOPT_POST, true);
                curl_setopt($con, CURLOPT_POSTFIELDS, $content);
            }

            curl_setopt($con, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($con, CURLOPT_HTTPHEADER, $headers);


            $response = curl_exec($con);
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

namespace requests {

    use data\invoice_data;
    use Exception;
    use profitapi\request_type;

    class sale_invoice_create_request extends request
    {
        /**
         * Default constructor
         * @param $invoice_data invoice_data
         * @throws Exception when required fields are missing
         */
        public function __construct($invoice_data)
        {
            // if (!$invoice_data->validate())
            //   throw new Exception("Invalid data supplied");

            parent::__construct(request_type::POST_JSON,null, json_encode($invoice_data->getData()));
        }

        function getContext()
        {
            return "sales/invoices";
        }
    }
    abstract class request
    {
        private $header_array;
        private $content;
        private $type;

        /**
         * request constructor.
         * @param $type string as declared in profitapi/request_type
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
}

namespace data {
    const GUID_REGEX_PATTERN = "\b[0-9a-f]{8}\b-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-\b[0-9a-f]{12}\b";

    function verifyGUID($guid) {
        return preg_match($guid, GUID_REGEX_PATTERN);
    }


    /* DOCUMENTATION: https://doc.profit365.eu/developers/en/api/doc/sales/invoices#section4-row */
    class invoice_row_data extends data {

        public function __construct($quantity, $price)
        {
            $data = array();
            $data["quantity"] = $quantity;
            $data["price"] = $price;


            parent::__construct($data);
        }


        public function itemId($var) {
            $this->data["itemId"] = $var;
        }
        public function code($var) {
            $this->data["code"] = $var;
        }
        public function name($var) {
            $this->data["name"] = $var;
        }
        public function vatParagraphID($var) {
            $this->data["vatParagraphID"] = $var;
        }


    }
    /* DOCUMENTATION: https://doc.profit365.eu/developers/en/api/doc/sales/invoices#section4 */
    class invoice_data extends data {
        /**
         * @param $dateCreated string ISO8601
         * @param $invoice_row_data array of rows
         * @param $ordnerId string GUID of ordner this invoice belongs to
         * @param $warehouseId string GUID of warehouse where this invoice belongs to
         */
        public function __construct($dateCreated, $invoice_row_data, $ordnerId = null, $warehouseId = null)
        {
            $data = array();
            $data["dateCreated"] = $dateCreated;
            $data["rows"] = $invoice_row_data;

            if($ordnerId != null)
                $data["ordnerId"] = $ordnerId;
            if($warehouseId != null)
                $data["warehouseId"] = $warehouseId;

            parent::__construct($data);
        }

        public function ordnerId($var) {
            $this->data["ordnerId"] = $var;
        }
        public function warehouseId($var) {
            $this->data["warehouseId"] = $var;
        }
        public function rows($var) {
            $this->data["rows"] = $var;
        }
        public function recordNumber($var) {
            $this->data["recordNumber"] = $var;
        }
        public function tags($var) {
            $this->data["tags"] = $var;
        }
        public function partnerId($var) {
            $this->data["partnerId"] = $var;
        }
        public function partnerDetail($var) {
            $this->data["partnerDetail"] = $var;
        }
        public function partnerAddress($var) {
            $this->data["partnerAddress"] = $var;
        }
        public function deliveryTypeId($var) {
            $this->data["paymentTypeId"] = $var;
        }
        public function paymentTypeId($var) {
            $this->data["ordnerId"] = $var;
        }
        public function bankAccountId($var) {
            $this->data["bankAccountId"] = $var;
        }
        public function symbolVariable($var) {
            $this->data["symbolVariable"] = $var;
        }
        public function symbolConstant($var) {
            $this->data["symbolConstant"] = $var;
        }
        public function symbolSpecific($var) {
            $this->data["symbolSpecific"] = $var;
        }
        public function dateAccounting($var) {
            $this->data["dateAccounting"] = $var;
        }
        public function dateDelivery($var) {
            $this->data["dateDelivery"] = $var;
        }
        public function periodId($var) {
            $this->data["periodId"] = $var;
        }
        public function dateValidTo($var) {
            $this->data["dateValidTo"] = $var;
        }
        public function commentAboveItems($var) {
            $this->data["commentAboveItems"] = $var;
        }
        public function commentBelowItems($var) {
            $this->data["commentBelowItems"] = $var;
        }
        public function vatParagraphID($var) {
            $this->data["vatParagraphID"] = $var;
        }
        public function bonusPercent($var) {
            $this->data["bonusPercent"] = $var;
        }
        public function orderRecordNo($var) {
            $this->data["orderRecordNo"] = $var;
        }
        public function warehouseRecordNo($var) {
            $this->data["warehouseRecordNo"] = $var;
        }
        public function costCenterId($var) {
            $this->data["costCenterId"] = $var;
        }
        public function costUnitId($var) {
            $this->data["costUnitId"] = $var;
        }
        public function personId($var) {
            $this->data["personId"] = $var;
        }
        public function projectId($var) {
            $this->data["projectId"] = $var;
        }
    }

    abstract class data {
        protected $data;

        /**
         * data constructor.
         * @param $data
         */
        public function __construct($data = array())
        {
            $this->data = $data;
        }

        /**
         * @return mixed
         */
        public function getData()
        {
            return $this->data;
        }




    }

}