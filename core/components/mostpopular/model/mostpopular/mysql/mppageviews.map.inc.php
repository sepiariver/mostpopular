<?php
/**
 * @package mostpopular
 */
$xpdo_meta_map['MPPageViews']= array (
  'package' => 'mostpopular',
  'version' => '1.1',
  'table' => 'mp_pageviews',
  'extends' => 'xPDOSimpleObject',
  'fields' => 
  array (
    'resource' => NULL,
    'datetime' => 'CURRENT_TIMESTAMP',
    'data' => '[]',
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
      'phptype' => 'json',
      'default' => '[]',
    ),
  ),
  'indexes' => 
  array (
    'resource' => 
    array (
      'alias' => 'resource',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'resource' => 
        array (
          'length' => '',
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
    'datetime' => 
    array (
      'alias' => 'datetime',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'datetime' => 
        array (
          'length' => '',
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
  ),
);
