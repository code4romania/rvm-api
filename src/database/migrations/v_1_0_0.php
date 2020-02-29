<?php
/* Import the couch DB specific functions. */
require 'dependencies/couch_db.php';


/**
 * Function that performs all DB changes.
 *
 * @return void
 */
function migrate(){
  createDB('rvm');
  createDB('users');
  createDB('statics');
  createDB('organisations');
  createDB('resources');
  createDB('volunteers');
  createDB('courses');
  createDB('allocations');
  createDB('institutions');
}


/**
 * Function that discards all DB changes.
 *
 * @return void
 */
function rollback(){
  deleteDB('institutions');
  deleteDB('allocations');
  deleteDB('courses');
  deleteDB('volunteers');
  deleteDB('resources');
  deleteDB('organisations');
  deleteDB('statics');
  deleteDB('users');
  deleteDB('rvm');
}


/******************************************/
/***************** START ******************/
/******************************************/
switch ($command) {
  case "":
    printf($messages['no-cmd']);
    break;

  case "migrate":
    printf($messages['migrate']);
    migrate();
    break;

  case "rollback":
    printf($messages['rollback']);
    rollback();
    break;

  default:
    printf($messages['invalid-cmd'], $command);
    break;
}

printf($messages['finish'], $argv[0]);
