MostPopular
===========

MostPopular is a set of session-aware tools that track MODX Revolution page views with customizable settings to improve accuracy and relevancy:

- Logs page hits asynchronously (or not) and responds with JSON (or nothing)
- By default, records page views per session, so that return visitors do not get logged until their MODX session expires
- Fetch MODX Resource IDs with the most (or least) pageviews within a given time period. According to the [PHP docs](http://php.net/manual/en/function.strtotime.php), "Parse about any English textual datetime description".
- Optionally log custom data with each page view
- sessionTimeout setting limits bot-like hits
- Fetch the number of hits for a specific Resource
- Fetch and template Resource page views, including datetime, number of views, and logged custom data
- Fetch and template Resources with page view count

Perfect for "Most Popular Posts" widgets, but also allows for numerous other use cases, especially with the ability to log arbitrary data along with page hits.

## Installation

Install via MODX Extras Installer or download from the [MODX Extras repo](https://modx.com/extras/package/mostpopular)

## Snippet Properties

### mpLogPageView

Properties:

- 'usePostVars' | (bool) | default depends on Resource content type | set to true for ajax pageview logging
- 'sessionVar' | (string) | system setting | if empty, no rate-limiting or session persistence happens. Make empty with caution!
- 'sessionTimeout' | (int) | 5 | in an effort to catch programmatic requests. 5 seconds seems reasonable. 0 disables but use with caution!
- 'resource' (required) | (int) | current Resource | gets Resource ID form POSTed resource, falling back to Snippet property, falling back to current Resource
- 'respond' | (bool) | default depends on Resource content type | response is returned (as JSON), otherwise empty string
- 'allowedDataKeys' | (string) | '' | comma-separated list of allowed keys in the array of data to log. This is required to  log any custom data, if 'usePostVars' is true.
- 'logData' | (string) | '' | JSON-formatted string, passed to the Snippet call, to log with the page hit. Gets processed with `$modx->fromJSON`, failing which nothing will be logged. Nested objects will be removed to limit logged data to 'allowedDataKeys' only.
- 'skipCrawlers' | (bool) | true | flag to enable/disable checking for crawlers before logging page view
- 'skipUAs' | (string) | 'GoogleBot, Bingbot, Slurp, Yahoo, DuckDuckBot, Baiduspider, YandexBot, Sogou, Exabot, Konqueror, facebot, facebookexternalhit, ia_archiver, wget' https://www.keycdn.com/blog/web-crawlers/ | comma-separated list of user agent strings to detect
- 'ipThrottle' | (int) | 30 | number of requests per minute at which to throttle logging of page views from the same IP address

mpLogPageView returns early if an invalid resource ID is provided, or a session variable exists for the Resource ID, or multiple requests in the same session occur within the sessionTimeout period, or the IP throttle is triggered.

### mpResources

This Snippet operates in 4 "modes" (see below) depending on properties defined.

Properties:

- 'separator' | (string) | '' | output separator
- 'toPlaceholder' | (string) | '' | key of placeholder to which to send output instead of returning
- 'resource' | (int) | 0 | affects operation mode. Scopes returned data to a specific Resource.
- 'tpl' | (string) | '' | setting this affects operation mode, and formats each item in the result set with the named Chunk
- 'limit' | (int) | 20 | limit the number of results returned
- 'sortDir' | (string) | 'DESC' | order by most page views or least page views, or datetime, depending on mode
- 'fromDate' | (string) | '' | use English textual description of the start date, after which page views will be returned. See http://php.net/manual/en/function.strtotime.php for examples.
- 'toDate' | (string) | '' | use English textual description of the end date, before which page views will be returned. See http://php.net/manual/en/function.strtotime.php for examples.
- 'exclude' | (string) | '' | comma-separated list of Resource IDs to explicitly exclude. Only used for mode 00

#### Possible Return Values

mpResources can execute in 4 "modes" depending on the properties passed to it:

1. DEFAULT: no 'resource' property, nor 'tpl'. Returns a comma-separated list of the IDs of the most (or least) popular Resources. This can be passed to the 'resources' property of another Snippet, like getResources. To sort your getResources result set the same way as the mpResources Snippet, you'll want to do this:

```
&sortby=`FIELD(modResource.id,[[mpResources]])`
&sortdir=`ASC`
```

2. SINGLE: 'resource' set, no 'tpl'. Returns a single number, which is the number of page views for a given Resource

3. VIEWS: 'resource' set, with a Chunk name in the 'tpl' property. Returns a formatted list of page views for the given Resource.

4. RESOURCES: no 'resource', with a Chunk name in the 'tpl' property. Returns a formatted list of most (or least) popular Resources, with page view count.

At this time the Snippet cannot fetch Resources with no page views, because hits are stored in a custom table and that's all the Snippet interacts with.

#### Available Placeholders

If a Chunk name is provided in the 'tpl' property, the following placeholders are set.

**Without specifying a Resource ID**
- All standard Resource fields
- 'views' | total number of views tracked

**With a Resource ID specified**
- 'id' | ID of the page view record.
- 'resource' | ID of viewed Resource
- 'datetime' | datetime at which this particular view was tracked
- 'data' | use dot notation for placeholder keys, to access logged data properties. For example: `[[+data.goodkey]]`
- 'ip' is unset

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

The above fetches the _least_ popular Resources that have been tracked (at least 1 page view) from 1 week ago to 5 hours ago, and formats the Resource and page view data with a Chunk called "mpResourcesTpl".
