MostPopular
===========

MostPopular is a set of session-aware tools that track MODX Revolution page views with customizable settings to improve accuracy and relevancy:

- Logs page hits asynchronously (or not) and responds with JSON (or nothing)
- sessionTimeout setting limits bot-like hits
- By default, records page views per session, so that return visitors do not get logged until their MODX session expires
- Fetch an ordered list of MODX Resource IDs with the most (or least) pageviews
- Fetch the number of hits for a given Resource
- Fetch and template Resource page views, including datetime, number of views, and logged custom data

Perfect for "Most Popular Posts" widgets, but also allows for numerous other use cases, extended by the ability to log arbitrary data along with page hits.

## Installation

Install via MODX Extras Installer

## Snippet Properties

### mpLogPageView

'usePostVars' | (bool) | true | set to true for ajax pageview logging
'sessionVar' | (string) | system setting | if empty, no rate-limiting or session persistence happens. Make empty with caution!
'sessionTimeout' | (int) | 5 | in an effort to catch programmatic requests. 5 seconds seems reasonable. 0 disables but use with caution!
'resource' (required) | (int) | current Resource | gets Resource ID form POSTed resource, falling back to Snippet property, falling back to current Resource
'respond' | (bool) | true | response is returned (as JSON), otherwise empty string
'allowedDataKeys' | (string) | empty string | comma-separated list of allowed keys in the array of data to log. This is required to  log any custom data, if 'usePostVars' is true.
'logData' | (string) | empty string | JSON-formatted string, passed to the Snippet call, to log with the page hit. Gets processed with `$modx->fromJSON`, failing which nothing will be logged.

mpLogPageView returns early if an invalid resource ID is provided or a session variable exists for the resource ID or multiple requests in the same session occur within the sessionTimeout period

### mpResources

'separator' | (string) | empty string | output separator
'toPlaceholder' | (string) | empty string | key of placeholder to which to send output instead of returning
'resource' | (int) | 0 | only fetch page views for a specific Resource ID. cast for cleaning.
'tpl' | (string) | empty string | setting this makes mpResources fetch all columns from the mp_pageviews table, and formats each item in the result set with the named Chunk
'limit' | (int) | 20 | limit the number of results returned
'sortDir' | (string) | 'DESC' | order by most page views or least page views
'fromDate' | (string) | empty string | use English textual description of the start date, after which page views will be returned. See http://php.net/manual/en/function.strtotime.php for examples.
'toDate' | (string) | 'now' | use English textual description of the end date, before which page views will be returned. See http://php.net/manual/en/function.strtotime.php for examples.
