DEVELOPING AND TESTING JAVASCRIPT

1. Install npm for your platform:
   http://nodejs.org/

2. Install the Chrome browser for your platform:
   https://www.google.com/intl/en/chrome/browser

3. Install ChromeDriver for your platform:
   https://sites.google.com/a/chromium.org/chromedriver/home

4. Install the Grunt command-line interface:
   npm install -g grunt-cli
   (this must be done as root on *nix systems)

5. cd .../snow_profile/js/.npm
   Node software is kept in a hidden directory to prevent name collisions
   with Drupal software.

6. npm install

7. You should now be able to use grunt to lint, document and test.
   lint: This is the default grunt task.  It uses ESLint.  The configuration is
     stored in conf/eslint.json and derives from Drupal 8, but allows "alert"
     and "confirm".
   doc: Uses jsdoc 3.  HTML output is stored in doc/ .  Read it with a
     browser starting at doc/index.html .
   test: Uses Chrome with Selenium Webdriver to provide a functional test.
     The URL tested is exported by test/lib/index.js.  Edit this file to use
     the right URL for your site.  File test/lib/test.html is a static HTML
     file that tests the JavaScript without calling any Drupal functions.
     The jQuery and SVG JavaScript libraries are loaded from the normal Drupal
     directories.
