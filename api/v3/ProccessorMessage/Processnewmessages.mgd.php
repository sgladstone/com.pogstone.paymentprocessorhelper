<?php
// This file declares a managed database record of type "Job".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// http://wiki.civicrm.org/confluence/display/CRMDOC42/Hook+Reference
return array (
  0 => 
  array (
    'name' => 'Cron:ProccessorMessage.Processnewmessages',
    'entity' => 'Job',
    'params' => 
    array (
      'version' => 3,
      'name' => 'Process data from payment processors',
      'description' => 'Process any new (or unprocessed) messages/data from a payment processor. Currently tested with Authorize.net ARB, PayPal and eWay reccurring',
      'run_frequency' => 'Hourly',
      'api_entity' => 'ProccessorMessage',
      'api_action' => 'Processnewmessages',
      'parameters' => '',
    ),
  ),
);