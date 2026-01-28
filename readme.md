# Ruigehond embed parent

Lightweight companion for the full [Ruigehond embed](https://github.com/joerivanveen/ruigehond-embed) plugin, to use on the embedding sites.

When enabled, use the shortcode \[ruigehond-embed-parent src="&lt;Iframe src&gt;"] to embed urls from sites where the full version of ruigehond-embed is installed.

Note: if you add `domain="any value"` to the shortcode it will _add the domain_ it is currently on to the iframe src, in the form of a slug.

Example: if you put `https://parent-site.com/test` in the src attribute, and you are visiting from ‘child-site.com’, the actual iframe src will be `https://parent-site.com/test-child-site-com`.

Subdomains will be passed as well, except www.

Tip: you can always inspect the iframe to see what is exactly requested, and adapt your shortcode or main site to that.
