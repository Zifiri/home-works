<?php

include "vendor/autoload.php";

use App\Reporter;

try {
    $reporter = new Reporter();
    //Username && Passport
    $reporter->setUsername("demo@bumin.com.tr");
    $reporter->setPassword("cjaiU8CV");

    // Token File
    $reporter->setTokenFile(dirname(__FILE__) . '/token.json');
    $reporter->getTokenFromCache();

} catch (Exception $e) {
    echo $e->getMessage();
}

//
try {

    // # REPORT
    $report = array(
        "fromDate" => "2018-04-10",
        "toDate" => "2019-04-10",
    );

    //print_r($reporter->transActionReport($report));

    // # LIST
    $list = array(
        "fromDate" => "2018-04-10",
        "toDate" => "2019-04-10",
        "merchant" => 1,
        "acquirer" => 1,
        "status" => "APPROVED",
        "paymentMethod" => "CREDITCARD",
        "page" => 1
    );
    // print_r($reporter->transActionList($list));

    // # TRANSACTION
    $id = "982786-1503662147-3";
    //   print_r($reporter->transAction($id));

    // # CLIENT
    $id = "982786-1503662147-3";
    // print_r($reporter->getClient($id));


    // Jwt
     print($reporter->getJwt());


} catch (Exception $e) {
    echo $e->getMessage();
}


