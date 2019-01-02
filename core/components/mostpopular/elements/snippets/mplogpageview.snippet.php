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

// PATHS
$mpPath = $modx->getOption('mostpopular.core_path', null, $modx->getOption('core_path') . 'components/mostpopular/');
$mpPath .= 'model/mostpopular/';

// GET SERVICE
if (file_exists($mpPath . 'mostpopular.class.php')) $mostpopular = $modx->getService('mostpopular', 'MostPopular', $mpPath, $scriptProperties);
if (!($mostpopular instanceof MostPopular) || !$modx->resource) {
    $modx->log(modX::LOG_LEVEL_ERROR, '[mpLogPageView] could not load the required MostPopular class!');
    return;
}

// DEFAULTS
$jsonResource = ($modx->resource->get('contentType') === 'application/json');
$usePostVars = ($jsonResource) ? true : false;
$respond = ($jsonResource) ? true : false;

// OPTIONS
/* set to true for ajax pageview logging*/
$usePostVars = $modx->getOption('usePostVars', $scriptProperties, $usePostVars);
/* if empty, no rate-limiting or session persistence happens. Make empty with caution! */
$sessionVar = $modx->getOption('sessionVar', $scriptProperties, $modx->getOption('mostpopular.session_var_key'));
/* in an effort to catch programmatic requests. 5 seconds seems reasonable. */
$sessionTimeout = $modx->getOption('sessionTimeout', $scriptProperties, $modx->getOption('mostpopular.session_timeout', null, 5));
/* POSTed resource falls back to Snippet property falls back to current Resource */
$resource = ($usePostVars) ? (int) $modx->getOption('resource', $_POST, 0, true) : (int) $modx->getOption('resource', $scriptProperties, $modx->resource->get('id'), true);
/* response is returned (as JSON), otherwise '' */
$respond = $modx->getOption('respond', $scriptProperties, $respond);
/* Attempt to skip crawlers? */
$skipCrawlers = $modx->getOption('skipCrawlers', $scriptProperties, true);
if ($skipCrawlers) {
    /* comma-separated list of user agents to skip */
    $skipUAs = $modx->getOption('skipUAs', $scriptProperties, 'GoogleBot, Bingbot, Slurp, Yahoo, DuckDuckBot, Baiduspider, YandexBot, Sogou, Exabot, Konqueror, facebot, facebookexternalhit, ia_archiver, wget');
    $skipUAs = $mostpopular->explodeAndClean($skipUAs, ',', 'strtolower');
    /* return early if user agent string matches a defined $crawler */
    $ua = strtolower($_SERVER['HTTP_USER_AGENT']);
    foreach ($skipUAs as $crawler) {
        if (strpos($ua, $crawler) !== false) return;
    }
}
/* Attempt throttling logging of requests from same IP */
$ipThrottle = $modx->getOption('ipThrottle', $scriptProperties, 30, true);

/* return early if invalid resource ID or
 * session variable exists for resource ID or
 * multiple requests within sessionTimeout period or
 * IP throttle is triggered
 */
if ($resource < 1) return;
if (!empty($sessionVar) && isset($_SESSION[$sessionVar][$resource])) return;
if (($sessionTimeout > 0) && ($_SESSION['mp_last_view'] + abs($sessionTimeout) > time())) return;
$ip = $modx->getOption('HTTP_X_FORWARDED_FOR', $_SERVER, $modx->getOption('REMOTE_ADDR', $_SERVER, ''), true);
if ($ipThrottle > 0) {
    $window = time() - 60; // hard-code 1-minute
    $ipq = $modx->newQuery('MPPageViews');
    $ipq->where([
        'ip:=' => $ip,
        'datetime:>' => $window
    ]);
    if ($modx->getCount('MPPageViews', $ipq) > $ipThrottle) return;
}

/* setting allowedDataKeys is required, if usePostVars is true */
$allowedDataKeys = $modx->getOption('allowedDataKeys', $scriptProperties, $modx->getOption('mostpopular.allowed_data_keys'));
/* ability to pass logData as a property of the Snippet call */
$logData = $modx->fromJSON($modx->getOption('logData', $scriptProperties, ''));
if (!is_array($logData)) $logData = array();

// PAGE VIEW OBJECT
$pageview = $modx->newObject('MPPageViews');
if (!$pageview) {
    $modx->log(modX::LOG_LEVEL_ERROR, '[mpLogPageView] could not create MPPageViews object!');
    return;
}

// FORMAT DATA
$pv = array(
    'resource' => $resource,
    'ip' => $ip,
);
if (!empty($allowedDataKeys)) {
    // Only pass through allowedDataKeys
    $allowedDataKeys = array_flip($mostpopular->explodeAndClean($allowedDataKeys));
    $data = ($usePostVars) ? modX::sanitize($_POST, $modx->sanitizePatterns) : $logData;
    $pv['data'] = array_intersect_key($data, $allowedDataKeys);
} else {
    // Only skip allowedDataKeys if using internal data source
    if (!empty($logData)) $pv['data'] = $logData;
}
// Never allow nested arrays
if (isset($pv['data'])) {
    foreach ($pv['data'] as $k => $v) {
        $pv['data'][$k] = (is_array($v)) ? '' : (string) $v;
    }
}

// POPULATE OBJECT
$pageview->fromArray($pv);

// TINY RESPONSE
$response = [];

// LOG PAGE VIEW
if ($pageview->save()) { // pageview was logged
    $response['success'] = true;
    if ($sessionTimeout > 0) $_SESSION['mp_last_view'] = time();
    if (!empty($sessionVar)) $_SESSION[$sessionVar][$resource] = true;
} else {
    $response['message'] = 'Unknown error. The pageview could not be saved.';
}

// RETURN
return ($respond) ? $modx->toJSON($response) : '';
