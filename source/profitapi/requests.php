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
         * request constructor
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



    class request_type {
        const GET = 0;
        const POST = 1;
        const PUT = 2;
    }

    class sale_invoice_create_request extends request
    {
        /**
         * sale_invoice_create_request constructor
         * @param $invoice_data invoice_payload
         */
        public function __construct($invoice_data)
        {
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

    class sale_invoice_printout_request extends request {

        private $reportId;
        private $invoiceId;
        private $output;
        /**
         * sale_invoice_printout_request constructor
         * @param $reportId string
         * @param $output string default pdf
         * @param $invoiceId string
         */
        public function __construct($reportId, $invoiceId, $output = "pdf")
        {
            parent::__construct(request_type::GET, ["Accept: application/pdf"]);
            $this->reportId = $reportId;
            $this->invoiceId = $invoiceId;
            $this->output =  $output;

            echo $this->getContext(). "\n";
        }


        function getContext()
        {
            // https://jantexsro.profit365.eu/tools/reports/report.aspx?ID=8aa93c05-b52d-4aeb-a780-652c5e06fe26&RecordNumbers=20200001VYF&RecordID=&out=pdf&action=open&ft=process&mode=preview
            return "https://api.profit365.eu/1.6/reports/3051?output=pdf&3e14784e-0391-4449-b1c1-59b5a2eec036";
            // return "reports/$this->reportId" . "?output=$this->output" . "&recordID=$this->invoiceId";
        }
    }
    class sale_invoice_list_request extends request {
        private $page;

        /**
         * sale_invoice_list_request constructor
         * @param $page integer
         */
        public function __construct($page)
        {
            parent::__construct(request_type::GET);
            $this->page = $page;
        }


        function getContext()
        {
            return "sales/invoices/$this->page";
        }
    }


}
