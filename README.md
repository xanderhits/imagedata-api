# imagedata-api

## API calls ##
`GET /images`
Returns all images.

`POST /data/update`
Refresh the source file. Download a new copy, parse it (filter faulty lines) and save it to disk in JSON format, ready to serve. In case of success, also serves the new data file in full.

Requests are formatted as inspired by [Google's JSON styleguide](https://google-styleguide.googlecode.com/svn/trunk/jsoncstyleguide.xml).

**Successful request example**
```
{
	"status": "success",
	"message": null,
	"data":
	[
		{
			"title": "Item 1",
			"description": "Description 1",
			"image": "http:\/\/url.to.api\/data\/img\/10438039923_2ef6f68348_c.jpg"
		},
		{
			...
		}
	]
}
```
**Faulty request example**
```
{
	"status": "false",
	"message": "Something went wrong!"
}
```

## Data integrity ##
The source CSV file may contain faulty entries. As the assignment was not specific in *how* these faulty lines are defined, I have used the following cases.

1. A field does not contain enough fields (i.e. less than 3)
2. A field contains too many fields (i.e. more than 3)
3. The name or URL fields are empty (description is optional).
4. The image is not valid: image could not be successfully downloaded or was not even an image at all

After calling `/data/update`, the user will be presented with a JSON object that contains both the valid entries (i.e. that are actually saved) and the lines that were faulty, with the keys being the CSV line numbers and the values the error messages.

## Performance ##
1. Use of a lightweight microframework, having a small memory footprint.
2. Write the 'database' to local disk in a JSON file. Local disk has far better performance than a SQL or noSQL database, and in this case does not require its flexibility or power.
3. Serve all image files from a single host.
4. The `/images` request uses response caching based on the last modified date of the JSON file - i.e. the last time the database was updated.

## Exception handling ##
Faulty requests are handled by returning a JSON object with status: error and a message:

```
{
	"status": "false",
	"message": "derpaderp!"
 }
```
In test/development modes, exceptions will be output as HTML in the browser including stacktrace.
In acceptance/production modes, a user-friendly JSON will be shown.

Errors are logged with a certain level (debug, info, warning, error and so forth). Depending on the deployment config (`conf/configure_modes.php`) we can set the minimum log level. Errors are written to `php://stderr`.

## DTAP ##
`conf/configure_modes.php` contains various configuration settings based on the deployment mode. These include log enabling, minimum level for logging, debug mode on (i.e. show stack traces or just show generic JSON error message) and any other additional info.

## Libraries and dependencies ##
Composer is used as dependency manager. It manages and autoloads the Slim framework and a [custom CSV parser for PHP](https://github.com/kzykhys/PHPCsvParser). This is done as the regular PHP `fgetcsv` function has some issues (see PHPCsvParser page for more information).

Other dependancies are: `copy` must be able to retrieve a remote URL (`allow_url_fopen` must be set to true in the PHP configuration), `Fileinfo` plugin must be enabled in order to read if downloaded image is actually an image.
