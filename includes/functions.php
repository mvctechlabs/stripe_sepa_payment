<?php
include 'includes/constants.php';


/* removes spaces and special characters from text */

function sanitizeString($string, $removspaceOnly = false) {
    
    if($removspaceOnly){
        $string = preg_replace('/\s+/', '', $string); // remove spaces
    }else{
        $string = strtolower($string); // convert string to lowercase
         $string = preg_replace('/[^a-z0-9]+/', '', $string); // remove special characters
    
        $string = preg_replace('/\s+/', '', $string); // remove spaces
    }
    
    return $string;
}

/* generates a random string with specified lenght */
function random_strings($length_of_string)
{
 
    // String of all alphanumeric character
    $str_result = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
 
    // Shuffle the $str_result and returns substring
    // of specified length
    return substr(str_shuffle($str_result), 0, $length_of_string);
}
 
function ibanCurlValidator($ibanNumber){
    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => IBAN_URI.sanitizeString($ibanNumber, true),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => array(
        'Authorization: Basic ',/* Your iban calculator simple authorization username and password */
        'Cookie: SERVERID=http1-3'
    ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    $data =  json_decode($response);

    if($data->result == 'passed'){

        return [
            'line1'     => $data->bank_address,
            'country'   => $data->country,
            'city'      => $data->bank_city
        ];
    }
    return false;
}