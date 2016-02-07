Contao Simple News URLs
===================

Simple Contao extension to allow news URLs without its reader page fragment. Instead of having an URL like

```
example.org/reader-page/news-entry.html
```

all news entries will instead be reachable via their alias directly, e.g.

```
example.org/news-entry.html
```

This is done through Contao's `getPageIdFromUrl` hook. All links to news entries generated by Contao will also be rewritten to its short URL via Contao's `generateFrontendUrl` hook. Furthermore, this extension also implements a `parseArticles` hook which will generate a 301 redirect to the short URL, if the newsreader was accessed via the news' long URL. This avoids having duplicate content. You can change this behavior with the following setting in your `localconfig.php`:

```php
$GLOBALS['TL_CONFIG']['simpleNewsUrlsRedirect'] = …;
```

Valid values are `301`, `302`, `303` or `'canonical'`. The latter will not create a redirect, but insert a canonical tag into the `<head>` instead.

The extension also works with different URL suffixes, or none at all. 

### Requirements

The only requirement is, that you enable the `auto_item` parameter (it is enabled by default) and do not disable the usage of aliases (they are not disabled by default) in the settings.

### Note

The usage of the `generateFrontendUrl` hook might have a negative impact on the website's performance, since there will be an additional database query for every URL that Contao generates with additional parameters.
