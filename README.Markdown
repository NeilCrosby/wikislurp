# WikiSlurp

WikiSlurp is a project designed to allow site developers to easily leverage the awesome power of Wikipiedia's articles.

WikiSlurp is used as a web service that queries Wikipedia to return, in HTML format, portions of articles about a given subject.  It's designed to be slotted into any webserver able to run PHP5, even those on shared hosting accounts.  All requests to external services are heavily cached by default, with the actual cache time used being able to be changed by the site owner via the config file.

WikiSlurp is very much alpha software at present.  I'm using it on [Is Neil Annoyed By][1] and [The Ten Word Review][2] though, so it is usable.  If you have any comments, feature requests or patches please give them to me.  I'm all ears.

Point releases are available for download from [The Code Train][3], and the current development code is always available from the [GitHub Repository][4].

[1]: http://isneilannoyedby.com
[2]: http://thetenwordreview.com
[3]: http://thecodetrain.co.uk/code/wikislurp/
[4]: http://github.com/NeilCrosby/wikislurp/tree/master