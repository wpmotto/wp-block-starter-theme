<?php

class Theme_Setup
{
    public $version;
    private $config;

    public function __construct()
    {
        $this->version = wp_get_theme()->get('Version') ?? false;
        $this->config = require_once get_template_directory() . '/config.php';
        $this->add_actions();
    }

    public function add_actions()
    {
        add_action('after_setup_theme', array($this, 'editor_styles'));
        add_action('wp_enqueue_scripts', array($this, 'theme_styles'));
        add_action('wp_head', array($this, 'font_preload_link'));
        add_action('admin_init', function () {
            // Add styles inline.
            wp_add_inline_style('wp-block-library', $this->get_font_face_styles());
        });
    }

    public function config($key = null)
    {
        if (is_null($key))
            return $this->config;

        if (isset($this->config[$key]))
            return $this->config[$key];

        return null;
    }

    public function editor_styles()
    {
        // Add support for block styles.
        add_theme_support('wp-block-styles');
        // Enqueue editor styles.
        add_editor_style('style.css');
    }

    public function theme_styles()
    {
        $handle = 'wicblocks-style';
        // Register theme stylesheet.
        wp_register_style(
            $handle,
            get_template_directory_uri() . '/style.css',
            array(),
            $this->version
        );

        // Add styles inline.
        wp_add_inline_style($handle, $this->get_font_face_styles());

        // Enqueue theme stylesheet.
        wp_enqueue_style($handle);
    }

    public function get_font_face_styles()
    {
        $fonts = array_map(function ($font) {
            extract(pathinfo($font['src']));
            $lines = [];
            $lines[] = "@font-face{";
            $lines[] = "src: url('{$font['src']}') format('$extension');";
            unset($font['src']);
            foreach ($font as $prop => $value) {
                $lines[] = "font-$prop: $value;";
            }
            $lines[] = "}";
            return implode(PHP_EOL, $lines);
        }, $this->config('fonts'));

        return implode(PHP_EOL, $fonts);
    }

    public function font_preload_link()
    {
        $links = [];
        foreach ($this->config('fonts') as $font) {
            extract(pathinfo($font['src']));
            $href = esc_url($font['src']);
            $links[] = "<link rel='preload' href='$href' as='font' type='font/$extension' crossorigin>";
        }
        echo implode(PHP_EOL, $links);
    }
}

$theme = new Theme_Setup();

// Add block patterns
// require get_template_directory() . '/inc/block-patterns.php';
