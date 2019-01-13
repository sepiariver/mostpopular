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
$toDate = strtotime($modx->getOption('toDate', $scriptProperties, ''));
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

// MODE
$resource = abs($resource);
$mode = (empty($tpl)) ? '0' : '1';
$mode .= ($resource > 0) ? '1' : '0';

// QUERY
$c = $modx->newQuery('MPPageViews');

// DATETIME
if (!empty($fromDate)) {
    /* convert to string for mysql */
    $fromDate = strftime("%F %T", $fromDate);
    if ($fromDate) $c->where(['datetime:>=' => $fromDate]);
}
if (!empty($toDate)) {
    /* convert to string for mysql */
    $toDate = strftime("%F %T", $toDate);
    if ($toDate) $c->where(['datetime:<' => $toDate]);
}

// MODE
switch ($mode) {
    case '11':
        // Fetch all page views for a specific Resource sorted by datetime
        $c->where(['resource:=' => $resource]);
        $c->sortby('datetime', $sortDir);
        $c->limit($limit);

        // Execute
        $rows = $modx->getCollection('MPPageViews', $c);

        // Template each page view with a Chunk
        $output = [];
        foreach ($rows as $row) {
            $row = $row->toArray();
            unset($row['ip']);
            $row['data'] = $modx->fromJSON($row['data']);
            $output[] = $mostpopular->getChunk($tpl, $row);
        }
        $output = implode($separator, $output);
        break;
    case '10':
        // Fetch a set of resource objects ordered by number of page views
        $c->select('resource, COUNT(*) AS views');
        if (!empty($exclude)) $c->where(['resource:NOT IN' => $exclude]);
        $c->groupby('resource');
        $c->sortby('views', $sortDir);
        $c->limit($limit);

        // Execute
        $c->prepare();
        $stmt = $modx->query($c->toSQL());

        // Template each Resource with a Chunk, add views attribute
        // Better to use default mode and getResources of views not needed
        if ($stmt) {
            $output = [];
            $pvs = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            $ids = array_keys($pvs);
            $rq = $modx->newQuery('modResource');
            $rq->where(['id:IN'  => $ids]);
            $rq->sortby('FIELD(modResource.id, ' . implode(',', $ids) . ' )');
            $rows = $modx->getCollection('modResource', $rq);
            foreach ($rows as $row) {
                $row = $row->toArray();
                $row['views'] = $pvs[$row['id']];
                $output[] = $mostpopular->getChunk($tpl, $row);
            }
            $output = implode($separator, $output);
        }
        break;
    case '01':
        // Fetch page view count for single Resource
        $c->select('COUNT(*) AS views');
        $c->where(['resource:=' => $resource]);

        $output = $modx->getValue($c->prepare());
        break;
    case '00':
    default:
        // Fetch a set of resource IDs ordered by number of page views
        $c->select('resource, COUNT(*) AS views');
        if (!empty($exclude)) $c->where(['resource:NOT IN' => $exclude]);
        $c->groupby('resource');
        $c->sortby('views', $sortDir);
        $c->limit($limit);

        $c->prepare();
        $stmt = $modx->query($c->toSQL());

        // Since no tpl was specified, we return a comma-separated list
        if ($stmt) {
            $rows = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
            $output = implode($separator, $rows);
        }
        break;
}

// RETURN
if (empty($toPlaceholder)) return $output;
$modx->setPlaceholder($toPlaceholder, $output);
