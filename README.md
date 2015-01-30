# TW Sliders

Manage sliders. Plugged into WordPress' media libraries. Can create general sliders or post-specific sliders.

## Installation
If you're using Composer to manage WordPress, add this plugin to your project's dependencies. Run:
```sh
composer require trendwerk/tw-sliders 1.0.0
```

Or manually add it to your `composer.json`:
```json
"require": {
	"trendwerk/tw-sliders": "1.0.0"
},
```

## How to use
Go to Appearance -> Sliders to create and edit sliders. Sliders are by default also supported for pages.


## Template tags

### Post type support

Remove sliders for a post type

```php
remove_post_type_support( 'page', 'sliders' );
```

Add slider support for an existing post type or set it when registering

```php
add_post_type_support( 'page', 'sliders' );
```


### Hook

You might want some other HTML output than the default. You can use the filter `tw-sliders-output` for that.

```php
function my_slider_output($html,$args) {
	//Your HTML here.
}
add_filter('tw-sliders-output','my_slider_output',10,2);
```

**$html** The default HTML output

**$args** All information about the slider being displayed
