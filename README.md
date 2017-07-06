MostPopular
===========

MostPopular is a set of session-aware tools that track MODX Revolution page views with customizable settings to improve accuracy and relevancy:

- Logs page hits asynchronously (or not) and responds with JSON (or nothing)
- sessionTimeout setting limits bot-like hits
- By default, records page views per session, so that return visitors do not get logged until their MODX session expires
- Fetch an ordered list of MODX Resource IDs with the most (or least) pageviews
- Fetch the number of hits for a given Resource
- Fetch and template Resource page views, including datetime, number of views, and logged custom data

Perfect for "Most Popular Posts" widgets, but also allows for numerous other use cases, enabled by logging arbitrary data along with page hits.
