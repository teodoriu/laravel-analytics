# Retrieve data from Google Analytics

Using this package you can easily retrieve data from Google Analytics (GA4).

Here are a few examples of the provided methods:

```php
$start = \Illuminate\Support\Carbon::now()->startOfDay();
$end = \Illuminate\Support\Carbon::now()->endOfDay();

$totalUsers = ErlanCarreira\Analytics\Facades\Analytics::setDateRanges($start, $end)
                               ->setDimensions(
                                   [
                                       'hostName',
                                   ]
                               )
                               ->setMetrics(
                                   [
                                       'totalUsers',
                                   ]
                               )
                               ->runReport()
                               ->toArray();
```

## Installation

This package can be installed through Composer.

``` bash
composer require erlancarreira/laravel-analytics
```

Add in your .env

```bash
GOOGLE_CLOUD_PROJECT=project_id_from_google_console
GOOGLE_APPLICATION_CREDENTIALS=full_path_to_google_credentials_json
ANALYTICS_PROPERTY_ID=analytics_property_id
```

Optionally, you can publish the config file of this package with this command:

``` bash
php artisan vendor:publish --provider="ErlanCarreira\Analytics\AnalyticsServiceProvider"
```

The following config file will be published in `config/analytics.php`

```php
return [

    /*
     * The view id of which you want to display data.
     */
    'property_id' => env('ANALYTICS_PROPERTY_ID', null),

    /*
     * The amount of minutes the Google API responses will be cached.
     * If you set this to zero, the responses won't be cached at all.
     */
    'cache_lifetime_in_minutes' => 60 * 24,
];
```

## How to obtain the credentials to communicate with Google Analytics

### Getting credentials

The first thing you’ll need to do is to get some credentials to use Google API’s. I’m assuming that you’ve already created a Google account and are signed in. Head over to [Google API’s site](https://console.developers.google.com/apis) and click "Select a project" in the header.

Next up we must specify which API’s the project may consume. In the list of available API’s click "Google Analytics Data API". On the next screen click "Enable".

Now that you’ve created a project that has access to the Analytics API it’s time to download a file with these credentials. Click "Credentials" in the sidebar. You’ll want to create a "Service account key".

On the next screen you can give the service account a name. You can name it anything you’d like. In the service account id you’ll see an email address. We’ll use this email address later on in this guide. Select "JSON" as the key type and click "Create" to download the JSON file.

Save the json inside your Laravel project at the location specified in the `service_account_credentials_json` key of the config file of this package. Because the json file contains potentially sensitive information I don't recommend committing it to your git repository.

### Granting permissions to your Analytics property

I'm assuming that you've already created a Analytics account on the [Analytics site](https://analytics.google.com/analytics). Go to "User management" in the Admin-section of the property.

On this screen you can grant access to the email address found in the `client_email` key from the json file you download in the previous step. Read only access is enough.

### Getting the property id

The last thing you'll have to do is fill in the `property_id` in the config file. You can get the right value on the [Analytics site](https://analytics.google.com/analytics). Go to "Property Settings" in the Admin-section of the property.

You'll need the `PROPERTY ID` displayed there.

## Usage

### All other Google Analytics queries

To perform all other queries on the Google Analytics resource use `runReport`.  [Google Analytics Data API (GA4)](https://developers.google.com/analytics/devguides/reporting/data/v1/basics) provides more information on which metrics and dimensions might be used.

[API Dimensions & Metrics](https://developers.google.com/analytics/devguides/reporting/data/v1/api-schema) that are available

You can get access to the underlying `BetaAnalyticsDataClient` object:

```php
ErlanCarreira\Analytics\Facades\Analytics::getAnalyticsService();
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email erlancarreira@hotmail.com instead of using the issue tracker.

## Credits

- [Freek Van der Herten](https://github.com/freekmurze)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
