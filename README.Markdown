# WikiSlurp

WikiSlurp is a project designed to allow site developers to easily leverage the awesome power of Wikipiedia's articles.

WikiSlurp is used as a web service that queries Wikipedia to return, in HTML format, portions of articles about a given subject.  It's designed to be slotted into any webserver able to run PHP5, even those on shared hosting accounts.  All requests to external services are heavily cached by default, with the actual cache time used being able to be changed by the site owner via the config file.

WikiSlurp is very much alpha software at present.  I'm using it on [Is Neil Annoyed By][1] and [The Ten Word Review][2] though, so it is usable.  If you have any comments, feature requests or patches please give them to me.  I'm all ears.

Point releases are available for download from [The Code Train][3], and the current development code is always available from the [GitHub Repository][4].

## Setup and Use

You'll need to set up a couple of keys to be able to get WikiSlurp to work.  Both keys are set in the `config/config.php` file, which you can create from the `config/config.php.example` file.

The first is the `SECRET` key.  This is what you use as your secret in the URL to call WikiSlurp.  You can make this whatever you like, but you must set it otherwise WikiSlurp will bail out with an error.

The second key that needs to be set is the `SEARCH_API_KEY`.  This is a Yahoo! BOSS API key, that you can get by signing up to Yahoo! BOSS (free, pretty easy to do).  BOSS is used to find the Wikipedia articles that we slurp.

WikiSlurp comes with an `example.php` file that you can use to test the system and make sure that things are happening in the way you expect.  In order to use the `example.php` file, you'll need to uncomment the line in the `.htaccess` file that forbids its use on production systems.

[1]: http://isneilannoyedby.com
[2]: http://thetenwordreview.com
[3]: http://thecodetrain.co.uk/code/wikislurp/
[4]: http://github.com/NeilCrosby/wikislurp/tree/master