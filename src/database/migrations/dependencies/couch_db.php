<?php
/* Import the general functions. */
include 'general.php';

/**
 * Function that extracts all the databases.
 * 
 * @return JSON
 */
function getAllDBS(){
  global $baseUrl, $user, $password;

  return _curl($baseUrl . '/_all_dbs', "GET", [], $user, $password);
}

/**
 * Function that creates a database.
 * 
 * @return TRUE  In case of success.
 *         FALSE In case of error.
 */
function createDB($name){
  global $baseUrl, $user, $password;

  $retVal = _curl($baseUrl . "/$name", "PUT", [], $user, $password);

  if(!$retVal){
    echo "Error o creation of $name DB.\n";
    return FALSE;
  } else {
    echo "DB $name created successfully.\n";
    return TRUE;
  }
}

/**
 * Function that deletes a database.
 * 
 * @return TRUE  In case of success.
 *         FALSE In case of error.
 */
function deleteDB($name){
  global $baseUrl, $user, $password;

  $retVal = _curl($baseUrl . "/$name", "DELETE", [], $user, $password);

  if(!$retVal){
    echo "Error o deletion of $name DB.\n";
    return FALSE;
  } else {
    echo "DB $name deleted successfully.\n";
    return TRUE;
  }
}
