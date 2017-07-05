<?php
/**
 * @package mostpopular
 */
$xpdo_meta_map['MPPageViews']= array (
  'package' => 'mostpopular',
  'version' => '0.1',
  'table' => 'mp_pageviews',
  'extends' => 'xPDOSimpleObject',
  'fields' => 
  array (
    'resource' => NULL,
    'datetime' => 'CURRENT_TIMESTAMP',
    'data' => '',
  ),
  'fieldMeta' => 
  array (
    'resource' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'attributes' => 'unsigned',
      'phptype' => 'integer',
      'null' => false,
      'index' => 'index',
    ),
    'datetime' => 
    array (
      'dbtype' => 'timestamp',
      'phptype' => 'timestamp',
      'default' => 'CURRENT_TIMESTAMP',
      'attributes' => 'ON UPDATE CURRENT_TIMESTAMP',
      'null' => false,
      'index' => 'index',
    ),
    'data' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '16378',
      'phptype' => 'string',
      'default' => '',
    ),
  ),
);
