## Shin Webpack WordPress Theme Boilerplate

Provides a boilerplate WordPress theme that features a webpack 5 build for CSS/SASS and JavaScript. The webpack configuration is defined in the [webpack.config.js](./webpack.config.js), and the compiled assets are included in the WordPress theme via code in the [functions.php](./functions.php).

## Development

After deploying and enabling the theme on a WordPress instance (see *Deploying the Theme* section below), install the theme dependencies by running the following command in the theme root (e.g. `/wp-content/themes/zippy`):

```
npm install
```

Note: the webpack build was created/tested with Node.js `v18.18.2` (LTS).

## Build

Various build commands are available that execute the webpack 5 builds:

- `npm run build` - development webpack build (`webpack --mode development`).
- `npm run dist` - production webpack build, with code minification enabled (`webpack --mode production`).
- `npm run dev` - development watch command.

## Deploying the Theme

This theme can be downloaded and deployed into a WordPress instance's themes directory (i.e. `/wp-content/themes`). Once the theme is deployed, run the build commands (`npm install` and `npm run dist`) to install and build the JavaScript and CSS.
