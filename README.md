MostPopular
===========

MostPopular is a set of session-aware tools that track MODX Revolution page views with customizable settings to improve accuracy and relevancy:

- Logs page hits asynchronously (or not) and responds with JSON (or nothing)
- By default, records page views per session, so that return visitors do not get logged until their MODX session expires
- Fetch an ordered list of MODX Resource IDs with the most (or least) pageviews within a given time period, for example: "Most popular posts within the last 7 days" or "Top 10 Resources of all time"
- Optionally log custom data with each page view
- sessionTimeout setting limits bot-like hits
- Fetch the number of hits for a specific Resource
- Fetch and template Resource page views, including datetime, number of views, and logged custom data

Perfect for "Most Popular Posts" widgets, but also allows for numerous other use cases, especially with the ability to log arbitrary data along with page hits.

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
'logData' | (string) | empty string | JSON-formatted string, passed to the Snippet call, to log with the page hit. Gets processed with `$modx->fromJSON`, failing which nothing will be logged. Nested objects will be removed to limit logged data to 'allowedDataKeys' only.

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

#### Available Placeholders
'resource' | (only without 'resource' property set) ID of viewed Resource
'views' | total number of views tracked
'datetime' | (only with 'resource' property set) datetime at which this particular view was tracked
'data' | (only with 'resource' property set) use dot notation for placeholder keys, to access logged data properties. For example: `[[+data.goodkey]]`

At this time the Snippet cannot fetch Resources with no page views, because hits are stored in a custom table and that's all the Snippet interacts with.

#### Possible Return Values

mpResources can execute in 4 "modes" depending on the properties passed to it:

- A comma-separated list of the IDs of the most (or least) popular Resources. This can be passed to the 'resources' property of another Snippet, like getResources. To sort your getResources result set the same way as the mpResources Snippet, you'll want to do this:
```
&sortby=`FIELD(modResource.id, [[mpResources]])`
```
- A single number, which is the number of page views for a given Resource
- If a Chunk name is provided to the 'tpl' property, a formatted list of page views for a given Resource 
- If a Chunk name is provided to the 'tpl' property, a formatted list of most (or least) popular Resources  

## Example Usage

_Note: due to the nature of mpLogPageView, you generally want to call it as uncacheable_

```
[[!mpLogPageView?
    &allowedDataKeys=`data,goodkey`
    &usePostVars=`0`
    &logData=`{
        "data":{"test":"nested objects are replaced with an empty string"},
        "goodkey":"only strings allowed",
        "badkey":"this doesn't get logged"}
    `]]
```

The above would be called on the Resource you want to track, because it won't use POST variables. You can set the data to log, manually in the Snippet property, with valid JSON.

```
[[mpResources?
    &tpl=`mpResourcesTpl`
    &limit=`10`
    &sortDir=`ASC`
    &fromDate=`-1 week`
    &toDate=`-5 hours`
]]

```

The above fetches the _least_ popular Resources that have been tracked (at least 1 page view) and formats the page view data with a Chunk called "mpResourcesTpl".
