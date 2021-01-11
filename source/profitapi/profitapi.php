<?php

namespace profitapi {

    include_once("requests.php");
    include_once("data.php");

    use requests\request;
    use requests\request_type;
    use requests\auth_header;
    use data\payload;

    class communicator
    {
        const API_VERSION = "1.6";
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
         * @return payload response code
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

            curl_exec($con);
            $code = curl_getinfo($con, CURLINFO_RESPONSE_CODE);
            curl_close($con);
            return $code;
        }
    }
}



