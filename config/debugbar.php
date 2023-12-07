<?php

return [

    /*
     |--------------------------------------------------------------------------
     | Debugbar Settings
     |--------------------------------------------------------------------------
     |
     | Debugbar is enabled by default, when debug is set to true in app.php.
     | You can override the value by setting enable to true or false instead of null.
     |
     | You can provide an array of URI's that must be ignored (eg. 'api/*')
     |
     */

    'enabled' => true,
    'except'  => [
        'telescope',
        'horizon',
    ],

    /*
     |--------------------------------------------------------------------------
     | DataCollectors
     |--------------------------------------------------------------------------
     |
     | Enable/disable DataCollectors
     |
     */

    'collectors' => [
        'phpinfo'         => true,  // Php version
        'messages'        => true,  // Messages
        'time'            => true,  // Time Datalogger
        'memory'          => true,  // Memory usage
        'exceptions'      => true,  // Exception displayer
        'log'             => true,  // Logs from Monolog (merged in messages if enabled)
        'cache'           => true, // Display cache events
        'config'          => true, // Display config settings
        'request'         => true, // Display request settings
        'query'           => true, // Display query settings
        'route'           => true,  // Current route information
        'views'           => true,  // Views with their data
        'db'              => true,  // Show database (PDO) queries and bindings
        'auth'            => false, // Display Laravel authentication status
        'gate'            => true,  // Display Laravel Gate checks
        'session'         => true,  // Display session data
        'symfony_request' => true,  // Only one can be enabled..
        'mail'            => true,  // Catch mail messages
        'events'          => false, // All events fired
        'files'           => false, // Show the included files

        'default_request' => false,  // Display models
    ],

    /*
     |--------------------------------------------------------------------------
     | Extra options
     |--------------------------------------------------------------------------
     |
     | Configure some DataCollectors
     |
     */

    'options' => [
        'auth'  => [
            'show_name' => true,   // Also show the users name/email in the debugbar
        ],
        'db'    => [
            'with_params'             => true,
            // Render SQL with the parameters substituted
            'backtrace'               => true,
            // Use a backtrace to find the origin of the query in your files.
            'backtrace_exclude_paths' => [],
            // Paths to exclude from backtrace. (in addition to defaults)
            'timeline'                => false,
            // Add the queries to the timeline
            'duration_background'     => true,
            // Show shaded background on each query relative to how long it took to execute.
            'explain'                 => [                 // Show EXPLAIN output on queries
                'enabled' => false,
                'types'   => ['SELECT'],     // Deprecated setting, is always only SELECT
            ],
            'hints'                   => false,
            // Show hints for common mistakes
            'show_copy'               => false,
            // Show copy button next to the query
        ],
        'mail'  => [
            'full_log' => false,
        ],
        'views' => [
            'timeline'      => false,  // Add the views to the timeline (Experimental)
            'data'          => true,    //Note: Can slow down the application, because the data can be quite large..
//            'data' => false,    //Note: Can slow down the application, because the data can be quite large..
            'exclude_paths' => [], // Add the paths which you don't want to appear in the views
        ],
        'route' => [
            'label' => true,  // show complete route on bar
        ],
        'cache' => [
            'values' => true, // collect cache values
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Editor
    |--------------------------------------------------------------------------
    |
    | Choose your preferred editor to use when clicking file name.
    |
    | Supported: "phpstorm", "vscode", "vscode-insiders", "vscode-remote",
    |            "vscode-insiders-remote", "vscodium", "textmate", "emacs",
    |            "sublime", "atom", "nova", "macvim", "idea", "netbeans",
    |            "xdebug", "espresso"
    |
    */

    'editor' => 'phpstorm',

    /*
    |--------------------------------------------------------------------------
    | Remote Path Mapping
    |--------------------------------------------------------------------------
    |
    | If you are using a remote dev server, like Laravel Homestead, Docker, or
    | even a remote VPS, it will be necessary to specify your path mapping.
    |
    | Leaving one, or both of these, empty or null will not trigger the remote
    | URL changes and Debugbar will treat your editor links as local files.
    |
    | "remote_sites_path" is an absolute base path for your sites or projects
    | in Homestead, Vagrant, Docker, or another remote development server.
    |
    | Example value: "/home/vagrant/Code"
    |
    | "local_sites_path" is an absolute base path for your sites or projects
    | on your local computer where your IDE or code editor is running on.
    |
    | Example values: "/Users/<name>/Code", "C:\Users\<name>\Documents\Code"
    |
    */

    'remote_sites_path' => '/data/app/backend/current',
    'local_sites_path'  => 'E:\Projects\7NM\YO\01.SRC\app-yo-docker-new\cms\YMT-app-cms',

    /*
     |--------------------------------------------------------------------------
     | Vendors
     |--------------------------------------------------------------------------
     |
     | Vendor files are included by default, but can be set to false.
     | This can also be set to 'js' or 'css', to only include javascript or css vendor files.
     | Vendor files are for css: font-awesome (including fonts) and highlight.js (css files)
     | and for js: jquery and highlight.js
     | So if you want syntax highlighting, set it to true.
     | jQuery is set to not conflict with existing jQuery scripts.
     |
     */

    'include_vendors' => true,

    /*
     |--------------------------------------------------------------------------
     | Capture Ajax Requests
     |--------------------------------------------------------------------------
     |
     | The Debugbar can capture Ajax requests and display them. If you don't want this (ie. because of errors),
     | you can use this option to disable sending the data through the headers.
     |
     | Optionally, you can also send ServerTiming headers on ajax requests for the Chrome DevTools.
     |
     | Note for your request to be identified as ajax requests they must either send the header
     | X-Requested-With with the value XMLHttpRequest (most JS libraries send this), or have application/json as a Accept header.
     */

    'capture_ajax'    => true,
    'add_ajax_timing' => false,

    /*
     |--------------------------------------------------------------------------
     | DebugBar route prefix
     |--------------------------------------------------------------------------
     |
     | Sometimes you want to set route prefix to be used by DebugBar to load
     | its resources from. Usually the need comes from misconfigured web server or
     | from trying to overcome bugs like this: http://trac.nginx.org/nginx/ticket/97
     |
     */
    'route_prefix'    => '_debugbar',
];
