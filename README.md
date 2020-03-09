# OBJECTIVE

This template has been implemented in order to develop complete SEO-friendly Helpsites that consume from the Inbenta KM API with the minimum configuration and effort. SEO-specific features are:

- 100% static (back-end) pages generation
- HTML5 semantic
- Web accessibility rules
- Customizable meta data for each page
- Dynamic sitemap generation
- robot.txt dynamic configuration


# FUNCTIONALITIES

Currently, the features provided by this application are:

- Categories pages
- Search results page
- Contents pages
- Popular contents list
- Push contents list
- Related contents
- Semantic autocomplete
- Labels and metadata's administration from Backstage's ExtraInfo
- Contents ratings (yes/no + comment)


# PLATFORM REQUIREMENTS

This template requires the following stack to properly work:

- PHP 7.0+
- Apache2 or Nginx
- [Composer](https://getcomposer.org/)
- [Memcached](https://www.php.net/manual/en/book.memcached.php) (optional but strongly recommended)


# BROWSERS SUPPORT

The following browsers are supported:

- IE 11+
- Last version of self-updating browsers:
  - Chrome
  - Firefox
  - Safari
  - Edge


# DEPENDENCIES

This application uses the following Composer dependencies:

- `slim/slim@^3.12`
- `symfony/http-foundation@^4.3`
- `slim/php-view@^2.2`
- `ext-memcached@*`
- `guzzlehttp/guzzle@^6.3`

Please note that if you do not have any available Memcached server, the `FileCached` class (located in `src/Utils/FileCached.php`) can be used as an alternative. You must then change the `cache` service in `src/conf/services.php` to use this class, and specify the `--ignore-platform-reqs` when performing a `composer install`.


# INSTALLATION

This template has been design be deployed seamlessly on the [Heroku](https://www.heroku.com/) platform. However, you can host it on any other platform or server just by changing a few configuration elements.

## INSTALLING ON HEROKU

1. Create a new app on the Heroku Dashboard, or directly using Heroku CLI.
2. Define necessary config vars for the newly created app. List of all the config vars to define is available in the `.env.example` file.
3. Add the [Memcachier](https://elements.heroku.com/addons/memcachier) addon to your Heroku app.
3. Push your Inbenta server-side Helpsite git repository onto the Heroku app's repository.

## INSTALLING ON OTHER PLATFORMS
1. Configure your Apache2/Nginx server so that it can serve the `public` directory at the root of this respository. Caveat: `mod_headers` and `mod_rewrite` must be enabled to make this template work.
2. Define necessary config vars in a `.env` file at the root of this repository. List of all the config vars to define is available in the `.env.example` file.
3. You should probably update the `src/conf/ini.php` file to tell PHP to load config vars from your newly created `.env` file.


# DEVELOPMENT

## STRUCTURE

The server-side HelpSite template is mainly based on the [Slim 3 microframework](https://www.slimframework.com/), which offers both flexibility and performance. Repository is structured as followed:

### `/assets`

This directory contains all the static assets (images, fonts, CSS, JavaScript, ...) unminified and uncompressed, for development. For production, you should minify and optimize those assets, using a tool like [JSCompress](https://jscompress.com/), and put all those assets into the `/public/assets` directory.

### `/public`

This directory contains the public part of the HelpSite. Everything inside it will be statically served. By default, if a URL does not correspond to a static asset, `index.php` will be used as a fallback.

### `/src`

This directory contains the actual HelpSite's back-end code. All the PHP code and configuration must go there. It is composed of several sub-directories:

- **/src/conf**: Configuration-related files
- **/src/Controllers**: Pages-related logic files
- **/src/Exceptions**: Custom exceptions thrown by the HelpSite
- **/src/locale**: Language-related files
- **/src/Middlewares**: Logic that should be called before / after any HTTP request
- **/src/Services**: Inbenta APIs clients, translation service, HTML generation, ...
- **/src/Utils**: Utilitaries functions and methods
- **/src/Views**: HTML templates for each component / page

## CUSTOMIZING THE SERVER-SIDE HELPSITE

One of the main strenghts of this template is its flexibility. You can indeed change from labels or styling to complete pages very easily.

### Labels

All the application's labels can be customized from both locale files (located in `/src/locale`), and from Backstage's ExtraInfo, if applicable. To locally define your labels, just create a new directory with the accronym of the language (e.g. `en`, `fr`, `es`, ...) based on the same structure as the default language directory (`en`). In order to leverage on Backstage's ExtraInfo labels management, please contact Inbenta.

### Basic configuration

Some basic application configuration elements can be changed using the `/src/conf/app.php`, such as display options, metadata, user type, source, ratings, ...

### Advanced customization

If simple styling, display, or language options do not fit your needs, you can go further in HelpSite customization by changing HTML templates, or even pages logic. To do so, we recommand that you create a `/src/Custom`, and reproduce the same structure as in `/src` (e.g. having a `Controllers` directory for you custom controllers, a `Views` directory for your custom views, and so on). Each of your new classes should extend the default ones, have the same name, and be located in the `Inbenta\ServerSideHelpsite\Custom` namespace. This structuration will make template upgrades to newer versions easier.

For instance, if you want to change the HelpSite's homepage structure:
1. Create a `/src/Custom` directory
2. Create a `/src/Custom/Controllers` directory
3. Create a `/src/Custom/Views` directory
4. Create a `/src/Custom/Controllers/Home.php` file
5. In that file, declare your `Home` class in the `Inbenta\ServerSideHelpsite\Custom\Controllers` namespace, that extends the default `Home` class (in `Inbenta\ServerSideHelpsite\Controllers` namespace)
6. Implement your own logic in the `getHomePage` method
7. Create a `/src/Custom/Views/header.html` file
8. In that file, put your HTML code for the custom header template

### Best practices

- You should always check that all events are correctly tracked in Backstage when you perform advanced customizations
- You should always ensure that performance is good (using the built-in `Monitoring` middleware) when you perform advanced customizations
- You should cache as many API calls as possible, using `Memcached` or `FileCached` services
- You should never cache API responses that return tracking codes, as reusing them can cause inconsistencies in logs
