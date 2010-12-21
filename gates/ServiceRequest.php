<?php
// include the PEAR package (http://pear.php.net/package/http_request/)
require_once 'HTTP/Request.php';

// dummy function to hide warning messages
function MaskErrors() {}

// function to return errors as a response XML document
function ReturnError($request,$error) {
    $doc = new DOMDocument();
    $doc->loadXML('<SERVICE status="FAILED" error_msg="'.$error.'" />');
    return $doc;
}

// FUNCTION
//  ServiceRequest = send an HTTP service request and receive an XML response
// INPUT
//   $host = String = the URL of the service
//   $request = String = the XML request
// OUTPUT
//   $doc = XML Document
function ServiceRequest($host,$request,$bypass_headers = false) {
    // create request object
    $req = &new HTTP_Request($host);
    // use POST method
    $req->setMethod(HTTP_REQUEST_METHOD_POST);
    // request well formed HTTP response headers
    $req->addHeader('cygnus_dotnet_client', '');
    // request header processing
    $req->setBypassHeaders($bypass_headers);
    // set the request string
    $req->setBody($request);
    // send the request
    $rc = $req->sendRequest();
    if (PEAR::isError($rc)) {
        return ReturnError($request,'Connection Failure');
    }
    // get the response
    $result = $req->getResponseBody();
    // convert the response string into an XML document
    set_error_handler('MaskErrors');
    $doc = new DOMDocument();
    if (!$doc->loadXML($result)) {
        return ReturnError($request,'Invalid XML Response');
    }
    restore_error_handler();
    //  make sure a SERVICE tag exists
    $nodes=$doc->getElementsByTagName('SERVICE') ;
    if ($nodes->length==0) {
        return ReturnError($request,'Missing return SERVICE tag');
    }
    // make sure a STATUS was returned
    if (strlen($nodes->item(0)->getAttribute('status'))==0) {
        return ReturnError($request,'Missing retrun STATUS attribute');
    }
    // looks good, return it
    return $doc;
}
?>