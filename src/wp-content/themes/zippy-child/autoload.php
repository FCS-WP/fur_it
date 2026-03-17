<?php

/**
 * Autoload non-class PHP files from /inc (recursive)
 */
function theme_autoload_files($dir)
{
  $files = glob(trailingslashit($dir) . '*');

  if (!$files) return;

  foreach ($files as $file) {

    // If folder → load recursively
    if (is_dir($file)) {
      theme_autoload_files($file);

      // Load procedural PHP files
    } elseif (is_file($file) && pathinfo($file, PATHINFO_EXTENSION) === 'php') {
      require_once $file;
    }
  }
}

/**
 * PSR-4 Class Autoloader for /app namespace
 */
spl_autoload_register(function ($class) {

  // Zippy theme main namespace
  $prefix = 'App\\';

  // If class does not start with App\, skip
  if (strpos($class, $prefix) !== 0) {
    return;
  }

  // Remove namespace prefix → convert to path
  $relative_class = str_replace($prefix, '', $class);

  // Convert namespace separators → directory separators
  $relative_class = str_replace('\\', '/', $relative_class);

  // Build file path in /app
  $file = THEME_DIR_CHILD . '/app/' . $relative_class . '.php';

  if (file_exists($file)) {
    require_once $file;
  }
});

// Load all procedural files
theme_autoload_files(THEME_DIR_CHILD . '/inc');
