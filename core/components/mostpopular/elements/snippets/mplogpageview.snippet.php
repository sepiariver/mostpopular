<?php
/**
 * mpLogPageView
 *
 * Adds a record to the MPPageViews table.
 *
 * @package MostPopular
 * @author @sepiariver <info@sepiariver.com>
 * Copyright 2017 by YJ Tso
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, write to the Free Software Foundation, Inc., 59 Temple
 * Place, Suite 330, Boston, MA 02111-1307 USA
 **/

// OPTIONS
$usePostVars = $modx->getOption('usePostVars', $scriptProperties, true);
$sessionVar = $modx->getOption('sessionVar', $scriptProperties, $modx->getOption('mostpopular.session_var_key'));
$resource = ($usePostVars) ? (int) $modx->getOption('resource', $_POST, 0, true) : (int) $modx->getOption('resource', $scriptProperties, $modx->resource->get('id'), true);
if ($resource < 1) return;
if (isset($_SESSION[$sessionVar][$resource])) return;
$allowedDataKeys = $modx->getOption('allowedDataKeys', $scriptProperties, $modx->getOption('mostpopular.allowed_data_keys'));
$logData = $modx->fromJSON($modx->getOption('logData', $scriptProperties, ''));
if (!is_array($logData)) $logData = array();

// Paths
$mpPath = $modx->getOption('mostpopular.core_path', null, $modx->getOption('core_path') . 'components/mostpopular/');
$mpPath .= 'model/mostpopular/';

// Get Class
if (file_exists($mpPath . 'mostpopular.class.php')) $mostpopular = $modx->getService('mostpopular', 'MostPopular', $mpPath, $scriptProperties);
if (!($mostpopular instanceof MostPopular)) {
    $modx->log(modX::LOG_LEVEL_ERROR, '[mpLogPageView] could not load the required MostPopular class!');
    return;
}

// Load page view object
$pageview = $modx->newObject('MPPageViews');
if (!$pageview) {
    $modx->log(modX::LOG_LEVEL_ERROR, '[mpLogPageView] could not create MPPageViews object!');
    return;
}

// Format data
$pv = array(
    'resource' => $resource,
    'data' => $logData,
);
if (!empty($allowedDataKeys)) {
    $allowedDataKeys = array_flip($mostpopular->explodeAndClean($allowedDataKeys));
    if ($usePostVars) $pv['data'] = array_intersect_key($_POST, $allowedDataKeys);
}

$pageview->fromArray($pv);

// Attempt to save
if ($pageview->save()) {
    $success['success'] = true;
    $_SESSION[$sessionVar][$resource] = true;
} else {
    $success['message'] = 'Unknown error. The pageview could not be saved.';
}

return $modx->toJSON($success);