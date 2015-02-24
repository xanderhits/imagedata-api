# imagedata-api

## Application structure ##

## API calls ##
`GET /images/`
Returns all images.

`POST /data/update/`
Refresh the source file. Download a new copy, parse it (filter faulty lines) and save it to disk in JSON format, ready to serve. In case of success, also serves the new data file in full.

Requests are formatted as inspired by [Google's JSON styleguide](https://google-styleguide.googlecode.com/svn/trunk/jsoncstyleguide.xml).

**Successful request example**
```
{
	"status": "success",
	"message": null,
	"data":
	{
		{"Title1","desc1", "url1"},
		{"Title2","desc2", "url2"}
	}
}
```
**Faulty request example**
```
{
	"status": "false",
	"message": "derpaderp!"
}
```
## Performance ##
Micro-framework usage. How do we store our local copy of the database. File database, plain CSV file (fastest but least flexible), relational database?
Cached files are stored on the local disk.

## Image cache ##
Local file storage. What cache time is acceptable? We have to optimize the image size for our mobile app anyway: set a maximum image format. If it is below this, we must resize it.

## Exception handling ##
Faulty requests are handled by returning a JSON object with status: error and a message:

```
{
	"status": "false",
	"message": "derpaderp!"
 }
```

## DTAP ##
Load a config.{deploymenttype}.php file based on what the deployment mode is set to? Not sure yet...

## Libraries and dependancies ##
Composer is used as dependancy manager. It manages and autoloads the Slim framework and a [custom CSV parser for PHP](https://github.com/kzykhys/PHPCsvParser). This is done as the regular PHP ```fgetcsv``` function has some issues (see PHPCsvParser page for more information).