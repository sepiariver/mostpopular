<?php
/**
 * mpResources
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
$separator = $modx->getOption('separator', $scriptProperties, ',');
$limit = (int) $modx->getOption('limit', $scriptProperties, 20);
$sortDir = (strtoupper($modx->getOption('sortDir', $scriptProperties, 'DESC')) === 'ASC') ? 'ASC' : 'DESC';

// PATHS
$mpPath = $modx->getOption('mostpopular.core_path', null, $modx->getOption('core_path') . 'components/mostpopular/');
$mpPath .= 'model/mostpopular/';

// GET SERVICE
if (file_exists($mpPath . 'mostpopular.class.php')) $mostpopular = $modx->getService('mostpopular', 'MostPopular', $mpPath, $scriptProperties);
if (!($mostpopular instanceof MostPopular)) {
    $modx->log(modX::LOG_LEVEL_ERROR, '[mpLogPageView] could not load the required MostPopular class!');
    return;
}

$output = '';
$stmt = $modx->query("
    SELECT resource, COUNT(*) AS RESCOUNT
    FROM modx_mp_pageviews
    GROUP BY resource
    ORDER BY RESCOUNT " . $sortDir . "
    LIMIT " . $limit . "
");

if ($stmt) {
    $rows = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $output .= implode($separator, $rows);
}
return $output;