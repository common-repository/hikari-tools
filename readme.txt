=== Hikari Tools Framework ===
Contributors: shidouhikari 
Donate link: http://Hikari.ws/wordpress/#donate
Tags: tool, framework, plugin, dependancy, HkToolsOptions, HkTools, builder, options, HTML, JavaScript, development, hikari-tools, Drupal, wp_options, commentmeta, postmeta, usermeta, network, multisite, database, tool, uninstall, DRY, object oriented
Requires at least: 2.8.0
Tested up to: 3.0.1
Stable tag: 1.07.05

A plugin development framework with a lot of reusable code and a nice settings page builder.

== Description ==

**<a href="http://hikari.ws/tools/">Hikari Tools Framework</a>** isn't a plugin with features for the end user. It's a development framework with tools to be used by other plugins, so that they don't need to duplicate the same code over and over again.

It deeply decreases plugins development time, specially in building options pages. For that, instead of building the whole HTML for each plugin, we can just prepare an array and it's used to build the whole page.

Another great feature this framework offers is options detection and reset. With the use of another simple array, in the bottom of options page it prints a table showing to plugin's users all kinds of options the plugins creates, being it wp_options, comment meta, post meta, and even network-wide options and user specific options (usermeta).

Every kind of data your plugin stores in database is shown in a clear way, with its key so that users can easly search for them in database. But they don't need to, because together with each option it informs if there's any data of that type stored, and provides user-friendly command to reset them all, totally cleaning the user's database from any data created by the plugin. Very easy and practical to use, and instantly available to any plugin that consumes Hikari Tools Framework!


= Features =

There are really a bunch of features available, here are the most used ones.

* *DRY*: Using the Don't Repeat Yourself principle, features are developed only once in the framework and are used anywhere you want! And once the framework is updated, the update is automatically replicated to all plugins that use it!
* *Object Oriented*: it provides 2 classes, which you can expand to implement your code, and then call parent methods anywhere to use Framework's features. Also, if you wanna add special features to a specific method, just overwrite it in your class!
* *Automatic 'plugins_loaded' and 'init' actions*: follows Wordpress standards and runs the least code possible during plugins load time. Each object is instantiated and runs its <code>construct()</code> method during 'plugins_loaded' action, and its <code>startup()</code> method during 'init' action
* *Data dump*: great for debugging, you can use the <code>HkTools::dump()</code> method to pass any kind of data and have it dumped, with a title above it, anywhere you want! And there's also the <code>HkTools::formatCode()</code> that receives a string and prints it using formatting plugins if available, or *pre* HTML element if needed.
* *Strings search*: use <code>hasNeedle($haystack,$needle)</code> and <code>hasNeedleInsensitive($haystack,$needle)</code> methods to search inside strings. They use PHP's <code>stripos()</code> to do the search, but instead of returning numbers that are rarely used it returns ready-to-use boolean values to say if <code>$needle</code> was or wasn't found, and <code>$needle</code> can even be an array if you wanna search many strings at once!
* *Version checks*: the Framework tests PHP and Wordpress versions and doesn't load itself if requirements aren't fit, and plugins that consume it follow a standard of testing the Framework version too.
* *Paths to plugin files*: it provides 2 variables for local path and URL for plugins root folder, for each plugin that consumes the Framework!


**Full featured options page builder**: This one deserves its own features list, so many they are! The *HkToolsOptions* class handles all your options data and options page building, including reseting data to default:

* Instead of having in each of your plugins a complete HTML page to allow your users to setup configs, just create an array with your options data and everything will be handled by the Framework, **including handling default values and registering the options page and its menu item**!
* *Many types of data are supported*: text, textarea, select, radiobox, checkbox, and if neither of those are enough you can also specify custom areas where your special options (ex: complex data that requires custom HTML, JavaScript, etc) can be placed!
* *Store multiple settings in a single option using arrays*: forget those dumb plugins that add dozens of wp_options rows, now you can use a single array to store all those settings, and place them all in a single options variable. And you don't have to deal with the complexity of setting them to use array, because the Framework handles it to you!
* **Use multiple sections**: Does your plugin use so many settings that it becomes messy? Don't worry, create visually separated sections to organize your settings, each of them having a title and help text to explain what the section refers to. And multiple sections are still stored in a unique array'ed wp_options row!
* *Store your options in multiple places*: aside from the most known wp_options (which is specific for each site), you can also store Multisite / network-wide options (that are shared by all sites in a MultiSite network) and user specific options (which are stored together to current user and are valid only to him).
* Too many storage places make it hard for plugins users to delete them all when they are uninstalling the plugin? No! *Hikari Tools Framework easily resets them!*: it provides an **uninstall form** which is added in the bottom of default options page, listing all kinds of options (wp_options, sitemeta, usermeta, postmeta, commentmeta, ...) your plugin uses, informs for each of them if there's any data stored ATM, and provides a command to delete them all from database, followed by a command to directly uninstall the plugin! This feature can also be used to reset options to default.
* *Default options aren't stored*: many plugins use to add default options to database when they find none, therefore storing data that didn't need to be there and blocking users from deleting those data before deactivating the plugin. With **Hikari Tools Framework** plugins that doesn't happen, because it doesn't save data automatically and uses default settings when options data isn't found in database. *And that's totally transparent for you while handling options in your code!*
* *Special debug feature*: There's a property named <code>HkTools::debug</code> that's set to false by default. In your plugin you can develop debugging code that will only be run if <code>$this->debug</code> is set to true. Then, it's just a matter of setting it in your extended class and all of a sudden all debug will be available to you! BTW, options pages have "hidden" debug code, and when <code>HkToolsOptions::debug</code> is set to true they are automatically shown without you having to worry with anything!
* *Automatic options loading*: Always load your options object first, because its <code>HkTools::loadAllOptions()</code> method automatically loads your options and stores them in <code>HkTools::optionsDBValue</code>, *regardless if it was indeed loaded from database or was built from options defaults*. The class by default handles a single wp_options variable, but you can create more objects and overwrite <code>loadAllOptions()</code> to load and provide them all.
* *Options page customization*: if some part of default page doesn't fit your needs, you can change it by overwriting a <code>HkToolsOptions</code> method, instead of having to throw the whole class away and go back building full pages again.
* *<code>HkToolsOptions extends HkTools</code>*: of course, all features available in HkTools are also available in HkToolsOptions to be used for handling your options.
* Reset and uninstall commands use JavaScript to alert user about data lost and ask confirmation before deleting


I dedicate Hikari Tools Framework to Ju, my beloved frient ^-^


== Installation ==

**Hikari Tools Framework** requires at least *Wordpress 2.8* and *PHP5* to work.

You can use the built in installer and upgrader, or you can install the plugin manually.

1. Download the zip file, upload it to your server and extract all its content to your <code>/wp-content/plugins</code> folder. Make sure the plugin has its own folder (for exemple  <code>/wp-content/plugins/hikari-tools/</code>).
2. Activate the plugin through the 'Plugins' menu in WordPress admin page.


As already said, Hikari Tools Framework alone provides nothing for end user, therefore you'll notice no change by just installing it. ATM it doesn't even have its own options page.

But now you're ready to install and use plugins that require this Framework, and if you've already installed any of those (I'm sure many users will come to this page after installing any other of my plugins and notice it won't work before this one is installed :) ) you were seeing a message requesting to install the Framework. In this case, the message will now vanish and you can use your desired plugin normally.


= Upgrading =

If you have to upgrade manually, simply delete <code>hikari-tools</code> folder and follow installation steps again.

While deleted, all plugins that require it will stop working, but no data will be lost and as soon as the new version is extracted to its folder everything will go back to normal.

Always update **Hikari Tools Framework** first, to only then update other plugins.

= Uninstalling =

If you uninstall **Hikari Tools Framework** while another plugin that requires it is activated, that plugin will stop working and you'll se a message requesting to install the Framework.

Hikari Tools Framework itself currently stores no data in database and doesn't require special actions to be uninstalled, but you must first uninstall all plugins that require it. Those plugins don't work without it and won't be able to delete data they store in database until you install it back.

Since Wordpress doesn't support plugins depandancy to handle it properly, just make sure there's no plugin that uses the Framework before uninstalling it. In future versions I'm gonna develop something to control which plugins are using it and see what can be done to make it more user friendly.


== Frequently Asked Questions ==

= I've installed one of your plugins and now it's forcing me to install this one too. Why did you build incomplete plugins?! Is that allowed at all?? =

Chill, don't panic.

My plugins that require **Hikari Tools Framework** aren't incomplete, they just use the Framework to do some stuff and can't work without it.

In the past I used to add the framework in each of my plugins, but that consumes extra RAM and makes maintenance very hard to be done. It's much easier to have the Framework in its own plugin and just make others require it. And that message you see is just trying to help you, explaining why the plugin isn't working and how to complete its installation.

A plugin requiring aother isn't illegal. Drupal CMS for exemple uses it a lot, and Wordpress should incentive it too :)

= But I had your Framework plugin installed, then I updated another plugin and the "error" message is back!  =

That's also not a bad thing. In the same way a plugin requires a minimum Wordpress version to work (because it uses a function that was introduced in that version), a plugin may require a minimum version of the framework.

My plugins are in constant development (as I'm able to do it for free, of course), and new features are introduced in every new version.


I suggest always keeping my plugins updated to the last version, I know how boring it is when a new version of a software is worse than older ones and I do my best to make the last version the best of them all. If for some reason you prefere an older version, just talk to me saying what was changed that you didn't like and I'll try to solve it.

That being said, always update **Hikari Tools Framework** first, to only then update other plugins.

= Incredible! Can I use your framework for developing my own plugins? =

Sure. Just open its file and read its source, what each method does and what parameters they receive. Get one of my plugin and see how they consume the framework, and do the same.

You'll notice the options page builder is very flexible and you can change parts of it leaving the rest untouched, and there are many methods that can be overwritten to add new sections or change existing ones.

When you publish your plugin, just inform your users that they need to install this Framework to use it. And of course, please link **<a href="http://hikari.ws/tools/">Hikari Tools Framework</a>** original page.

= And what about themes? Can I use the framework in them? =

Yes, I didn't add it to my theme yet, but it can be used for side features (always keep modularity in mind: themes are meant to offer visual markup and not software features, but small features are ok), and for building theme options page.

Just be careful that, while plugins can block themselves and lay sleeping when the framework isn't available, themes must always be fully available. For that reason, in a big exception, you must check the existence of the framework as a plugin and if not found you may need to have a backup copy in the theme folder to be loaded. That's *deepy* discourage (I've done that for 1 year and know the problems that it creates) and should be used only on extreme situations. And when doing so, make sure to add a bright message in backend requesting user to install the framework plugin.

== Screenshots ==

**Hikari Tools Framework**  has no visual effect for users. These ScreenShots were taken from <a href="http://Hikari.ws/email-url-obfuscator/">Hikari Email & URL Obfuscator</a> options page.

1. A section area named Settings, with 2 settings of type *textarea* and another of type *combo*
2. Options page has a sidebar, with links about the plugins and a RSS feed. These links can be changed
3. A *combo* option and a *checkbox*, with button to save options and another button to reset them
4. The uninstall form
5. Message from a plugin that requires Hikari Tools Framework to work, asking for it to be updated

== Changelog ==

= 1.07 =
* First public release.

== Upgrade Notice ==

= 1.08 and above =
If you have to upgrade manually, simply delete <code>hikari-tools</code> folder and follow installation steps again.
