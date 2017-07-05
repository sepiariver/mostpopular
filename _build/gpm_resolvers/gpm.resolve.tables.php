<?php
/**
 * Resolve creating db tables
 *
 * THIS RESOLVER IS AUTOMATICALLY GENERATED, NO CHANGES WILL APPLY
 *
 * @package mostpopular
 * @subpackage build
 *
 * @var mixed $object
 * @var modX $modx
 * @var array $options
 */

if ($object->xpdo) {
    $modx =& $object->xpdo;
    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
        case xPDOTransport::ACTION_UPGRADE:
            $modelPath = $modx->getOption('mostpopular.core_path', null, $modx->getOption('core_path') . 'components/mostpopular/') . 'model/';
            
            $modx->addPackage('mostpopular', $modelPath, null);


            $manager = $modx->getManager();

            $manager->createObjectContainer('MPPageViews');

            break;
    }
}

return true;