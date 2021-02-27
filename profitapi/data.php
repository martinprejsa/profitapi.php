<?php
namespace profitapi {

    class payload
    {
        protected $content;

        /**
         * data constructor.
         * @param $data
         */
        public function __construct($data = array())
        {
            $this->content = $data;
        }

        /**
         * @return mixed
         */
        public function getContent()
        {
            return $this->content;
        }

        public function set($key, $val)
        {
            $this->content[$key] = $val;
        }
    }

    /* DOCUMENTATION: https://doc.profit365.eu/developers/en/api/doc/sales/invoices#section4-row */
    class invoice_row_payload extends payload
    {
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
    class invoice_payload extends payload
    {
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
