# HTTPS guide link for WordPress Health Check - for PRO ISP and other interested WordPress web hosts

WordPress 5.3 comes with a new <code>site_status_test_result</code> filter. This allows weh hosts to add a link to the Site Heath test results. This plugin utlizes this new filter to add a link to their guide page in case the test fails (https is not used or not configured). The filter was introduced in this Make WordPress Core Dev Note article: https://make.wordpress.org/core/2019/09/25/whats-new-in-site-health-for-wordpress-5-3/#highlighter_144139

It's language aware, and support any language version of the guide page.

The anchor of the added link is extracted from the guide document itself, no need to translate. The document title is retrieved directly from the document, using a configurable timout. The title is then cached for a configurable time. An English fallback title is provided in case of any failure to get an external resource. Alle configurations in a special section at the top of the file. Documented in source.

This plugin is made for PRO ISP, at own initiative. It's main php file is intended to be placed in <code>wp-content/mu-plugins</code> folder. End user may delete it whout any consequence except not seeing this link any more.

Suggested roll-out: To all sites with WordPress, PHP >= 5.6 and "Force HTTPS Redirect" not enabled in cPanel.

Issues and pull requests welcome.
