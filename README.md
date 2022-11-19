[![](https://img.shields.io/packagist/v/fritzmg/contao-simple-news-urls.svg)](https://packagist.org/packages/fritzmg/contao-simple-news-urls)
[![](https://img.shields.io/packagist/dt/fritzmg/contao-simple-news-urls.svg)](https://packagist.org/packages/fritzmg/contao-simple-news-urls)

Contao Simple News URLs
=======================

This Contao extension allows news URLs without its reader page fragment. Instead of having an URL like

```
example.org/reader-page/news-entry
```

all news entries will instead be reachable via their alias directly, e.g.

```
example.org/news-entry
```

when enabled in the respective news archive. There will also be a 301 redirect from the old URL to the new one.

_Note:_ the extension enforces these URLs, i.e. the URL prefix and suffix settings of the website root will be disregarded.
