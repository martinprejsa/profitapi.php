# profit365-php
PHP api made for interacting with the Profit365 online accounting application (work in progess)

# Usage

```php
<?php

use data\invoice_payload;
use data\invoice_row_payload;
use profitapi\auth_header;
use profitapi\auth_type;
use profitapi\communicator;
use requests\sale_invoice_create_request;

include_once "source/profitapi.php";
try {
    $header = new auth_header(
        auth_type::BASIC,
        "my-email@server.doman:supersecretpassword",
        "client secret",
        "client guid",
        "company id"
    );


    $comm = new communicator($header);

    $row0 = new invoice_row_payload("Orange juice", 2.5, 15);
    $row1 = new invoice_row_payload("Grapefruit juice", 2); // no quantity specified, used default of 1

    $data = new invoice_payload(
        date(DATE_ISO8601),
        array($row0->getData(), $row1->getData())
    );

    $data->partnerDetail("George Blue Livestreet 478 Acity");


    $request = new sale_invoice_create_request($data);

    if($comm->request($request) == 200) // returns response code
        echo "Created new invoice <br>";
    else
        echo "Failed to create new invoice <br>";

} catch (Exception $e) {
    echo $e->getMessage() . "<br>";
    die(1);
}
```