<?php
namespace data {

    const GUID_REGEX_PATTERN = "\b[0-9a-f]{8}\b-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-\b[0-9a-f]{12}\b";

    function verifyGUID($guid) {
        return preg_match($guid, GUID_REGEX_PATTERN);
    }

    class payload {
        protected $data;

        /**
         * data constructor.
         * @param $data
         */
        public function __construct($data = array()){
            $this->data = $data;
        }

        /**
         * @return mixed
         */
        public function getData()
        {
            return $this->data;
        }

        public function set($key, $val) {
            $this->data[$key] = $val;
        }
    }

    /* DOCUMENTATION: https://doc.profit365.eu/developers/en/api/doc/sales/invoices#section4-row */
    class invoice_row_payload extends payload {

        /**
         * invoice_row_data constructor.
         * @param $name string name of the item
         * @param $price double price of the item
         * @param $quantity integer quantity of the items
         */
        public function __construct($name, $price, $quantity = 1)
        {
            parent::__construct();
            $this->set("name", $name);
            $this->set("price", $price);
            $this->set("quantity", $quantity);
        }

    }
    /* DOCUMENTATION: https://doc.profit365.eu/developers/en/api/doc/sales/invoices#section4 */
    class invoice_payload extends payload {

        /**
         * invoice_data constructor.
         * @param $date string datetime formatted in ISO8601 format
         * @param $rows array of row data
         */
        public function __construct($date, $rows)
        {
            parent::__construct();
            $this->set("date", $date);
            $this->set("rows", $rows);
        }

    }
}
