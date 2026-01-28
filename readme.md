# Ruigehond embed parent

Lightweight companion for the full [Ruigehond embed](https://github.com/joerivanveen/ruigehond-embed) plugin, to use on the embedding sites.

When enabled, use the shortcode `[ruigehond-embed-parent src="&lt;Iframe src&gt;"]` to embed urls from sites where the full version of ruigehond-embed is installed.

## Domain dependent Iframe src

In addition to standard functionality also provided by the full plugin, this ‘parent’ plugin adds the option to have the final Iframe src depend on the domain that is currently requested.

Go to the settings to add specific src urls for specific domains.

Use the shortcode without the src attribute (just: `[ruigehond-embed-parent]`) to have the plugin automatically select the correct src url based on the current domain.
