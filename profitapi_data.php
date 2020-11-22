<?php

namespace data {
    class sale_invoice_data extends data
    {
        function required_fields()
        {
            return ["ordnerId", "warehouseId", "dateCreated", "rows", "rows.quantity", "rows.price", "rows.priceBrutto"];
        }
    }

    class partner_data extends data
    {
        public function __construct($data)
        {
            parent::__construct($data);
        }

        function required_fields()
        {
            return ["fullName", "countryCode", "postalCode", "city"];
        }
    }

    abstract class data
    {
        private $data;

        /**
         * data constructor.
         * @param $data mixed
         */
        public function __construct($data)
        {
            $this->data = $data;
        }

        public function validate()
        {
            foreach ($this->required_fields() as $key) {
                if (!$this->validate_key($key)) {
                    return false;
                }
            }
            return array_key_exists($this->getData(), $this->required_fields());
        }

        abstract function required_fields();

        private function validate_key($key)
        {
            if (($subkey = strstr($key, ".")) != false) {
                return $this->validate_key($subkey);
            } else
                return array_key_exists($key, $this->getData());
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

