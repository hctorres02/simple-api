# Simple-API
_A simple API (serious?)_

## Configuration:
* Update `config.php` file with your database infos:
```
$host  =  'localhost';
$dbname  =  'simple-api';
$user  =  'root';
$pass  =  '';
```

Now it is no longer necessary to inform your database schema, Simple API will read this information from the informed database using `information_schema.columns`.

## Usage
_This version doesn't use `friendly URLs`._

* GET: `index.php?resource={table_name}`
* GET: `index.php?resource={table_name}&id={id}`

Use HTTP verbs to `create`, `update` or `delete`.

Currently, HTTP verbs allowed are: `GET`, `POST`, `PUT`, `DELETE`.
Others verbs results in `status 409`.

## Roadmap
- [x] launch v0.1
- [ ] implements PSR-4
- [ ] improve DB class
