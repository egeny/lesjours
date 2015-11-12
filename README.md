Favicon: https://github.com/audreyr/favicon-cheat-sheet

Bug in the build process:
build:svg has to be done before the build:html should be launched but it is not the case (for streams reasons).
Launch `gulp build:svg` first and then `gulp` or `gulp build`.

TODO:
- configure csslint & eslint
- Added a PNG fallback generator