<?php
/**
 * @package mostpopular
 */
$xpdo_meta_map['MPPageViews']= array (
  'package' => 'mostpopular',
  'version' => '1.1',
  'table' => 'mp_pageviews',
  'extends' => 'xPDOSimpleObject',
  'tableMeta' => 
  array (
    'engine' => 'MyISAM',
  ),
  'fields' => 
  array (
    'resource' => NULL,
    'datetime' => 'CURRENT_TIMESTAMP',
    'data' => '[]',
    'ip' => '',
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
    'ip' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '2000',
      'phptype' => 'string',
      'default' => '',
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
    'ip' => 
    array (
      'alias' => 'ip',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'ip' => 
        array (
          'length' => '',
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
  ),
);
