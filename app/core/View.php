<?php
// FILE: /app/core/View.php

/**
 * View Class
 *
 * Handles view rendering with layouts
 */
class View {
    /**
     * Render a view with layout
     *
     * @param string $view View file path
     * @param array $data Data to pass to view
     * @param string $layout Layout file
     */
    public function render($view, $data = [], $layout = 'main') {
        // Extract data to variables
        extract($data);

        // Start output buffering for view content
        ob_start();

        $viewFile = __DIR__ . '/../views/' . $view . '.php';

        if (!file_exists($viewFile)) {
            die("View not found: $view");
        }

        require $viewFile;

        // Get view content
        $content = ob_get_clean();

        // Render layout with content
        if ($layout) {
            $layoutFile = __DIR__ . '/../views/layouts/' . $layout . '.php';

            if (file_exists($layoutFile)) {
                require $layoutFile;
            } else {
                echo $content;
            }
        } else {
            echo $content;
        }
    }
}
