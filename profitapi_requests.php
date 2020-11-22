<?php

namespace requests {

    use data\data;
    use Exception;

    class ordner_list_request extends request
    {

        private $page;
        /**
         * @param $page integer
         */
        public function __construct($page = 0)
        {
            $this->page = $page;
            parent::__construct(null, null);
        }

        function getContext()
        {
            return "catalogs/ordners/" . $this->page;
        }
    }

    class sale_invoice_create_request extends request
    {
        /**
         * Default constructor
         * @param $invoice_data data
         * @throws Exception when required fields are missing
         */
        public function __construct($invoice_data)
        {
            if (!$invoice_data->validate())
                throw new Exception("Invalid data supplied");

            parent::__construct(null, json_encode($invoice_data));
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

        /**
         * request constructor.
         * @param $header_array array of strings that represent headers (optional)
         * @param $content string of contents (optional)
         */
        public function __construct($header_array = null, $content = null)
        {
            $this->header_array = $header_array;
            $this->content = $content;
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