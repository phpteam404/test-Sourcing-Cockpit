# Installation Setup

To run AngularJS Gulp Version you will have to install following components in your environment.

Node.js: https://nodejs.org/en/
npm: https://npmjs.com
Gulp: http://gulpjs.com
Bower: https://bower.io

#Installing Bower and 3rd party plugins & components
Bower is a command line utility. Install it with npm.

##Install 3rd party plugins & components

$ bower install

##Run project
Go to project directory

$ cd Your_Project_directory

##Start local server and Gulp scripts
$ gulp serve

Your project is ready. Go to following url http://localhost:3000.

If gilp-sass not found : Run `npm install ` , `npm install -g gulp` and `npm install -g gulp-sass`.
## Development server
Run `gulp serve` for a dev server. Navigate to `http://localhost:3000/`. The app will automatically reload if you change any of the source files.

## Build

Run `gulp build` to build the project. The build artifacts will be stored in the `dist/` directory. Use the `-prod` flag for a production build.




