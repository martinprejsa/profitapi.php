# profit365-php
PHP api made for interacting with the Profit365 online accounting application. Currently supports only sale invoice related actions.

### Currently supported actions:
- Sale invoice creation
- Listing invoices
- Downloading invoice printouts

# Usage
```php
<?php

use data\invoice_payload;
use data\invoice_row_payload;
use profitapi\auth_header;
use profitapi\auth_type;
use profitapi\communicator;
use requests\sale_invoice_create_request;
use requests\sale_invoice_list_request;
use requests\sale_invoice_printout_request;

$comms = new communicator(new auth_header(
    auth_type::BASIC,
    "john.doe@profit365.eu:HT5tI2DfA9TviUmPwzw8eePVW0zgMv",
    "sc1vcOTTFLuqjFa5u08UKtKaWl48XSqlm8jMQvrnXnuPvRjqTPgIDI6P1YcR",
    "acd93523-b563-4b4b-b6cc-eb02d6230539",
    "a company id",
    false
));

// Example list invoices
$request = new sale_invoice_list_request(1);
if ($comms->request($request) != 200) {
    echo "Failed to retrieve listing of invoices";
    return;
}

$invoices = $comms->lastResult;

$invoiceObject = $invoices[0];

// Example end

// Example get an invoice printout from $invoiceObject that was returned in previous example
$request = new sale_invoice_printout_request("3051", $invoiceObject->id);

$handle = fopen("output.pdf", "w");
if ( $comms->request($request) != 200) {
    echo "Failed to retrieve a printout";
    return;
}


fwrite($handle, $comms->lastResult);
fflush($handle);
fclose($handle);

// Example end

// Example create an invoice
$rows = array(
    new invoice_row_payload("Grape juice", 1.25, 10)
);

$detailedRow = new invoice_row_payload("Gamer fuel", 10.2, 1);
$detailedRow->set("code", "some guid"); // Documentation for this object can be found at: https://doc.profit365.eu/developers/en/api/doc/sales/invoices#section4

$payload = new invoice_payload(date(DATE_ISO8601), $rows);
$payload->set("ordnerId", "another guid"); // Documentation for this object can be found at: https://doc.profit365.eu/developers/en/api/doc/sales/invoices#section4

$request = new sale_invoice_create_request($payload);
if($comms->request($request) != 200) {
    echo "Failed to create a new invoice";
    return;
} else {
    echo "Created new invoice";
}
```
