=== Debug Bar Plugin Activation ===
Contributors: jrf
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=995SSNDTCVBJG
Tags: debugbar, debug-bar, Debug Bar, plugin activation, plugin deactivation, uninstall, unexpected output
Requires at least: 3.8
Tested up to: 4.5
Stable tag: 1.0
License: GPLv2

Debug Bar Plugin Activation adds a new panel to the Debug Bar which displays plugin (de-)activation and uninstall errors.

== Description ==

Ever been "greeted" when you activated a plugin with the dreaded:

> This plugin generated # characters of **unexpected output** during activation....

And wondered what the unexpected output was ?

Or wondered whether a _de-activation_ or _uninstall_ routine was free of typical PHP errors ?

Well, no need to wonder anymore, as you can now see the output within your favorite debugging tool - the [Debug Bar](https://wordpress.org/plugins/debug-bar/).

Debug Bar Plugin Activation adds a new panel to the Debug Bar which displays the output generated during plugin activation, deactivation and uninstall.

Once you've fixed the issues, you can remove the logged output straight from the Debug Bar panel.
And when you uninstall a plugin, the associated logged activation and deactivation output entries will be removed automatically.


> This plugin was inspired by a conversation with [Mika Epstein](https://profiles.wordpress.org/ipstenu) during the [contributors day at WordCamp Europe 2016](https://2016.europe.wordcamp.org/introducing-the-wceu-2016-contributor-day-and-workshops/).


= Important =

This plugin requires the [Debug Bar](https://wordpress.org/plugins/debug-bar/) plugin to be installed and activated.

Also note that this plugin should be used solely for debugging and/or in a development environment and is not intended for use on a production site.

***********************************

If you like this plugin, please [rate and/or review](https://wordpress.org/support/view/plugin-reviews/debug-bar-plugin-activation) it. If you have ideas on how to make the plugin even better or if you have found any bugs, please report these in the [Support Forum](https://wordpress.org/support/plugin/debug-bar-plugin-activation) or in the [GitHub repository](https://github.com/jrfnl/debug-bar-plugin-activation/issues).



== Frequently Asked Questions ==

= Can it be used on live site ? =
This plugin is only meant to be used for development purposes, but shouldn't cause any issues if run on a production site.

= What is plugin (de-)activation ? =
> Activation and deactivation hooks provide ways to perform actions when plugins are activated or deactivated.

> Plugins can run an installation routine when they are activated in order to add rewrite rules, add custom database tables, or set default option values. ... The deactivation hook is best used to clear temporary data such as caches and temp directories.

Ref: [Plugin Handbook](https://developer.wordpress.org/plugins/the-basics/activation-deactivation-hooks/)

= What about uninstalling ? =
> Your plugin may need to do some clean-up when it is uninstalled from a site. A plugin is considered uninstalled if a user has deactivated the plugin, and then clicks the delete link.
>
> When your plugin is uninstalled, you'll want to clear out any rewrite rules added by the plugin, options and/or settings specific to to the plugin, or other database values that need to be removed.

Ref: [Plugin Handbook](https://developer.wordpress.org/plugins/the-basics/uninstall-methods/)

= How do I add activation, deactivation and uninstall routines to my plugin ? =
All the information you need is in the [Plugin Handbook on Plugin Basics](https://developer.wordpress.org/plugins/the-basics/).


= Why won't the plugin activate ? =
Have you read what it says in the beautifully red bar at the top of your plugins page ? As it says there, the Debug Bar plugin needs to be active for this plugin to work. If the Debug Bar plugin is not active, this plugin will automatically de-activate itself.


== Changelog ==

= 1.0 (2016-07-02) =
* Initial release.


== Upgrade Notice ==

= 1.0 =
* Initial release.


== Installation ==

1. Install Debug Bar if not already installed (https://wordpress.org/plugins/debug-bar/).
1. Extract the .zip file for this plugin and upload its contents to the `/wp-content/plugins/` directory. Alternatively, you can install directly from the Plugin directory within your WordPress Install.
1. Activate the plugin through the "Plugins" menu in WordPress.

Don't use this plugin on a live site. This plugin is **only** intended to be used for development purposes.


== Screenshots ==
1. Debug Bar Plugin Activation displaying error notices.


