# ![](src/img/logo.png) Les Jours

## Common tasks

| Task name             | Description |
|-----------------------|-------------|
| `default`             | Launch `clean` then `build` |
| `clean`               | Remove the `dist` folder |
| `build`               | Build the whole website. Launch sequentially `build:img`, `build:css:img`, `build:svg`, `build:css:svg`, `build:js` and `build:html` |
| `watch`               | Launch `build` then listen for changes in the `src` files (and subfolders) and launch the appropriate task (e.g. changing an HTML file will launch the `build:html` task) |

Edit the `gulpfile.js` to change the `root` variable if the website have to be hosted in a subfolder (e.g. `root = ''` if the website is hosted in `foo.com` ; `root = '/v1'` if the website is hosted in `foo.com/v1`).

## Advanced tasks

| Task name             | Description |
|-----------------------|-------------|
| `build:img`           | Launch sequentially `optimize:img` and `copy:img` |
| `build:css:img`       | Launch sequentially `optimize:css:img` and `copy:css:img` |
| `build:svg`           | Launch sequentially `optimize:svg`, `sprite:svg` and `copy:svg` |
| `build:css:svg`       | Launch sequentially `optimize:css:svg`, `sprite:css:svg` and `copy:css:svg` |
| `build:js`            | Minify and copy to `dist` all JS files present at the root of the `src/js` folder |
| `build:js:components` | Concatenate, minify and copy to `dist` all JS files present in a subfolder of the `src/js` folder |
| `build:html`          | Build the HTML pages from the `src/pages` folder and copy their assets. If a JSON file having the same name as the folder's name it will be used as data to inject. This file **must have** a `template` property matching a file in the `src/templates` folder. |
| `lint`                | Launch in parallel `lint:sass`, `lint:css` and `lint:js` |
| `lint:sass`           | Lint the `.scss` and `.sass` files in the `src/css` folder and subfolders |
| `lint:css`            | Lint the `.css` files in the `src/css` folder and subfolders |
| `lint:js`             | Lint the `.js` files in the `src/js` folder and subfolders |
| `optimize:img`        | Optimize the images (`.ico`, `.gif`, `.jpg` and `.png`) in the `src/img` folder (**will change** the files). Disabled for now since sometimes it destroys the images. |
| `optimize:css:img`    | Optimize the images (`.ico`, `.gif`, `.jpg` and `.png`) in the `src/css/img` folder (**will change** the files). Disabled for now since sometimes it destroys the images. |
| `copy:img`            | Copy the images files (`.ico`, `.gif`, `.jpg` and `.png`) from the `src/img` folder (and subfolders) to the `dist/img` folder (keeping subfolders) |
| `copy:css:img`        | Copy the images files (`.ico`, `.gif`, `.jpg` and `.png`) from the `src/css/img` folder (and subfolders) to the `dist/css/img` folder (keeping subfolders) |
| `optimize:svg`        | Optimize the SVG files in the `src/img` folder (**will change** the files) |
| `optimize:css:svg`    | Optimize the SVG files in the `src/css/img` folder (**will change** the files) |
| `sprite:svg`          | Sprite the SVG files present in subfolders of the `src/img` folder (e.g. SVG files in the `src/img/foo` folder will be compiled in a `src/img/foo.svg` file) |
| `sprite:css:svg`      | Sprite the SVG files present in subfolders of the `src/css/img` folder (e.g. SVG files in the `src/css/img/foo` folder will be compiled in a `src/css/img/foo.svg` file) |
| `copy:svg`            | Copy the SVG files from the `src/img` folder (and subfolders) to the `dist/img` folder (keeping subfolders) |
| `copy:css:svg`        | Copy the SVG files from the `src/css/img` folder (and subfolders) to the `dist/css/img` folder (keeping subfolders) |