<?php
/**
 * mpResources
 *
 * Fetches most popular resource IDs.
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
$toPlaceholder = $modx->getOption('toPlaceholder', $scriptProperties, '');
/* only fetch pageviews for a specific Resource ID. cast for cleaning. */
$resource = (int) $modx->getOption('resource', $scriptProperties, 0, true);
/* setting tpl fetches all columns for templating */
$tpl = $modx->getOption('tpl', $scriptProperties, '');
/* cast for cleaning */
$limit = (int) $modx->getOption('limit', $scriptProperties, 20);
/* normalize sortDir */
$sortDir = (strtoupper($modx->getOption('sortDir', $scriptProperties, 'DESC')) === 'ASC') ? 'ASC' : 'DESC';
/* these get processed later, before the query */
$fromDate = strtotime($modx->getOption('fromDate', $scriptProperties, ''));
$toDate = strtotime($modx->getOption('toDate', $scriptProperties, 'now'));
/* exclude resource IDs for mode 00 and cast */
$exclude = array_filter(array_map('intval', array_map('trim', explode(',', $modx->getOption('exclude', $scriptProperties, '')))));

// PATHS
$mpPath = $modx->getOption('mostpopular.core_path', null, $modx->getOption('core_path') . 'components/mostpopular/');
$mpPath .= 'model/mostpopular/';

// GET SERVICE
if (file_exists($mpPath . 'mostpopular.class.php')) $mostpopular = $modx->getService('mostpopular', 'MostPopular', $mpPath, $scriptProperties);
if (!($mostpopular instanceof MostPopular)) {
    $modx->log(modX::LOG_LEVEL_ERROR, '[mpLogPageView] could not load the required MostPopular class!');
    return;
}

// DATETIME
/* normalize bad inputs */
if ($fromDate === false) $fromDate = strtotime('1970-01-01');
if ($toDate === false) $toDate = time();
/* convert to string for mysql */
$fromDate = strftime("%F %T", $fromDate);
$toDate = strftime("%F %T", $toDate);

// EXCLUDE
$excludeSQL = (empty($exclude)) ? '' : "AND resource NOT IN (" . implode(',', $exclude) . ")";

// MODE
$resource = abs($resource);
$mode = (empty($tpl)) ? '0' : '1';
$mode .= ($resource > 0) ? '1' : '0';

// OUTPUT
switch ($mode) {
    case '11':
        // Fetch all page views for a specific Resource sorted by datetime
        $stmt = $modx->prepare("
            SELECT *
            FROM modx_mp_pageviews
            WHERE datetime >= ? 
            AND datetime < ? 
            AND resource = ?
            ORDER BY datetime ?
            LIMIT ?
        ");
        $stmt->execute([
            $fromDate,
            $toDate,
            $resource,
            $sortDir,
            $limit
        ]);
        // Template each page view with a Chunk
        if ($stmt) {
            $output = [];
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $row) {
                $row['data'] = $modx->fromJSON($row['data']);
                $output[] = $modx->getChunk($tpl, $row);
            }
            $output = implode($separator, $output);
        }
        break;
    case '10':
        // Fetch a set of resource objects ordered by number of page views
        $stmt = $modx->prepare("
            SELECT resource, COUNT(*) AS views
            FROM modx_mp_pageviews
            WHERE datetime >= :from 
            AND datetime < :to
            " . $excludeSQL . "
            GROUP BY resource
            ORDER BY views :dir
            LIMIT :lim
        ");
        $stmt->execute([
            'from' => $fromDate,
            'to' => $toDate,
            'dir' => $sortDir,
            'lim' => $limit
        ]);
        // Template each Resource and view count with a Chunk
        if ($stmt) {
            $output = [];
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $row) {
                $output[] = $modx->getChunk($tpl, $row);
            }
            $output = implode($separator, $output);
        }
        break;
    case '01':
        $stmt = $modx->prepare("
            SELECT COUNT(*) AS views
            FROM modx_mp_pageviews
            WHERE datetime >= ? 
            AND datetime < ? 
            AND resource = ?
            GROUP BY resource
        ");
        $stmt->execute([
            $fromDate,
            $toDate,
            $resource
        ]);
        // No tpl and specified Resource means we just return the number of page views for the Resource
        if ($stmt) {
            $rows = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
            $output = implode($separator, $rows);
        }
        break;
    case '00':
    default:
        // Fetch a set of resource IDs ordered by number of page views
        $stmt = $modx->prepare("
            SELECT resource, COUNT(*) AS views
            FROM modx_mp_pageviews
            WHERE datetime >= :from 
            AND datetime < :to
            " . $excludeSQL . "
            GROUP BY resource
            ORDER BY views :dir
            LIMIT :lim
        ");
        $stmt->execute([
            'from' => $fromDate,
            'to' => $toDate,
            'dir' => $sortDir,
            'lim' => $limit
        ]);
        // If no tpl was specified, we return a comma-separated list
        if ($stmt) {
            $rows = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
            $output = implode($separator, $rows);
        }
        break;
}

// RETURN
if (empty($toPlaceholder)) return $output;
$modx->setPlaceholder($toPlaceholder, $output);