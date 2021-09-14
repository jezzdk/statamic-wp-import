# Statamic WP Import

> Statamic WP Import can import simple content from your WP site.

Note: This addon works in conjunction with a [Wordpress plugin](https://github.com/jezzdk/wordpress-to-statamic-exporter).

## Disclaimer

This is basically a port of the official import script in Statamic v2, that has been wangjangled to work with v3. The only difference is that this does not import settings as globals, but this addons also downloads featured images.

## Features

This addon will:

- Create taxonomies and terms
- Create pages
- Create collections and entries

This addon will not:

- Create blueprints and fieldsets based on your ACF (or other custom field plugin) setup
- Create global sets and variables

All meta data that has been exported from Wordpress will be set as values on the entries. However, any meta data key prefixed with an underscore will be ignored. Also, inline images in post content will not be downloaded. See note about images below.

### Events

The addon is using the builtin methods for creating and saving content. As such, the normal events are dispatched which you can hook into for additional work according to your setup. That means you can listen on the following events to customize the import:

- `Statamic\Events\CollectionCreated`
- `Statamic\Events\CollectionSaved`
- `Statamic\Events\EntrySaving`
- `Statamic\Events\EntryCreated`
- `Statamic\Events\EntrySaved`
- `Statamic\Events\TaxonomySaved`
- `Statamic\Events\TermSaved`
- `Statamic\Events\AssetSaved`
- `Statamic\Events\AssetUploaded`

By the time you read this there might be others. Consult [the documentation](https://statamic.dev/extending/events#available-events) to learn more.

### A note regarding images

Only the featured image will be downloaded. Multiple featured images, images added with ACF and other plugins, are not downloaded. Featured images will be downloaded to the "assets" container into a folder called "imports/{collection_handle}", and saved on a field on the entry called "featured_image".

You can use the events above to do your own downloading of images and what not. I have done this myself with great success 👍

## How to Install

You can search for this addon in the `Tools > Addons` section of the Statamic control panel and click **install**, or run the following command from your project root:

``` bash
composer require jezzdk/statamic-wp-import
```

## How to Use

First of all, you must export your data using the [Export to Statamic Wordpress Plugin](https://github.com/jezzdk/wordpress-to-statamic-exporter). Check anything you wish to export, but have the notes above in mind.

Go to the `Tools > WP Import` section and upload the json file.

The summary will show you if anything was already found in your installation. If you choose to import it anyway, the content will be overwritten.

(De)Select anything you want and click "Import".

Done :)

## Config

The content of the config file looks like this:

```
<?php

return [

    /*
     * Enable downloading of featured image. The default is 'true'.
     */
    'download_images' => true,

    /*
     * Filter out meta data keys prefixed with '_'. The default is 'true'.
     */
    'exclude_underscore_data' => true,

];
```

You can publish it with the command:

`php artisan vendor:publish --tag=statamic-wp-import`

## Known issues

You might get timeout errors if you're importing large datasets and/or many images. In that case you might want to tweak the timeouts on your server or run the import locally.