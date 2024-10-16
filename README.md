# Statamic WP Import

> Statamic WP Import can import simple content from your WP site.

Note: This addon works in conjunction with a [WordPress plugin](https://github.com/jezzdk/wordpress-to-statamic-exporter).

## Note

This is a rough addon the Rad Pack inherited from [@jezzdk](https://github.com/jezzdk). It's based on the v2 method of generating a JSON file with a separate WP plugin, which isn't ideal, but should still be functional.

We're open to PRs and collaboration, though the Statamic Core Team is working on a different approach that will likely replace this in the near future.


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

Only the featured image will be downloaded. Multiple featured images, images added with ACF and other plugins, are not downloaded. Featured images will be downloaded to the "assets" container by default (change in config), into a folder called "{collection_handle}/{entry_slug}", and saved on a field on the entry called "featured_image".

You can use the events above to do your own downloading of images and what not. I have done this myself with great success 👍

## How to Install

You can search for this addon in the `Tools > Addons` section of the Statamic control panel and click **install**, or run the following command from your project root:

``` bash
composer require RadPack/wp-import
```

## How to Use

First of all, you must export your data using the [Export to Statamic Wordpress Plugin](https://github.com/statamic/wordpress-to-statamic-exporter). Check anything you wish to export, but have the notes above in mind.

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

    /**
     * The name of the assets container where images should be downloaded.
     */
    'assets_container' => 'assets',

    /*
     * Whether to skip download of an image if it already exist. The default is 'false'.
     */
    'skip_existing_images' => false,

    /*
     * Enable image overwriting. When set to false, a new image are created with a timestamp suffix, if the image already exists. The default is 'false'.
     */
    'overwrite_images' => false,

    /*
     * Filter out meta data keys prefixed with '_'. The default is 'true'.
     */
    'exclude_underscore_data' => true,

];
```

You can publish it with the command:

`php artisan vendor:publish --tag=wp-import`

## Known issues

You might get timeout errors if you're importing large datasets and/or many images. In that case you might want to tweak the timeouts on your server or run the import locally.
