<?php
/**
 * @package mostpopular
 */
require_once (strtr(realpath(dirname(dirname(__FILE__))), '\\', '/') . '/mppageviews.class.php');
class MPPageViews_mysql extends MPPageViews {}
?>