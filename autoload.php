<?php
/**
 * Simple PSR-4 autoloader for the Civicrm Demographics Census Comparison extension.
 */
spl_autoload_register(function (string $class): void {
    $prefix = 'Civicrm\\DemographicsCensusComparison\\';
    if (strpos($class, $prefix) !== 0) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $relativePath = str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass);
    $file = __DIR__ . '/src/' . $relativePath . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});
