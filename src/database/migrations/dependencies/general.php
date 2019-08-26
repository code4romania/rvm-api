<?php

/* Extract the cmd line arguments values. */
$shortopts = "b:u:p:c:";
$longopts  = ["base:", "user:", "pass:", "command:"];
$options = getopt($shortopts, $longopts);
/* Set the cmd line arguments values to appropiate values. */
$baseUrl = (isset($options['b'])) ? ($options['b']) : ((isset($options['base'])) ? ($options['base']) : ("localhost:5984/"));
$user = (isset($options['u'])) ? ($options['u']) : ((isset($options['user'])) ? ($options['user']) : ("admin"));
$password = (isset($options['p'])) ? ($options['p']) : ((isset($options['pass'])) ? ($options['pass']) : ("secret"));
$command = (isset($options['c'])) ? ($options['c']) : ((isset($options['command'])) ? ($options['command']) : (""));


/* Script messages. */
$messages = [ 'no-cmd' => "[ERROR]: Command not specified.\n"
            , 'invalid-cmd' => "[ERROR]: Command %s is not recognised.\n"
            , 'migrate' => "[INFO]: Migrate DB changes.\n"
            , 'rollback' => "[INFO]: Rollback DB changes.\n"
            , 'finish' => "[INFO]: Script %s finished successfully.\n"
            ];


/**
 * Function that performes am HTTp request using curl.
 * 
 * @param url:      The URL at which to perform the request.
 * @param method:   The HTTP method to use.
 * @param data:     Data to send over with the request.
 * @param username: The authentication user.
 * @param password: The authentication password.
 * 
 * @return JSON:  Decoded response data in case of success
 *         NULL:  In case of error.
 */
function _curl($url, $method, $data, $username, $password) {
  $curl = curl_init($url);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

  curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
  curl_setopt($curl, CURLOPT_USERPWD, $username . ":" . $password);

  switch ($method) {
    case "GET":
      curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
      curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
    break;
    case "POST":
      curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
      curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
    break;
    case "PUT":
      curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
      curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
    break;
    case "DELETE":
      curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE"); 
      curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
    break;
  }
  $response = curl_exec($curl);
  $data = json_decode($response);

  /* Check for 404 (file not found). */
  $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

  /* Check the HTTP Status code */
  switch ($httpCode) {
    case 200:
    case 201:
      echo "Success" . "\n";
      curl_close($curl);
      return ($data);
    break;
    case 404:
      echo "404: API Not found" . "\n";
      curl_close($curl);
      return NULL;
    break;
    case 500:
      echo "500: servers replied with an error." . "\n";
      curl_close($curl);
      return NULL;
    break;
    case 502:
      echo "502: servers may be down or being upgraded. Hopefully they'll be OK soon!" . "\n";
      curl_close($curl);
      return NULL;
    break;
    case 503:
      echo "503: service unavailable. Hopefully they'll be OK soon!" . "\n";
      curl_close($curl);
      return NULL;
    break;
    default:
      echo "Undocumented error: " . $httpCode . " : " . curl_error($curl) . "\n";
      curl_close($curl);
      return NULL;
    break;
  }
}
