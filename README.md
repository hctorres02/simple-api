# Simple-API
_A simple API (serious?)._

## Configuration:
* Update `config.php` file with your database infos:
```
$host  =  'localhost';
$dbname  =  'simple-api';
$user  =  'root';
$pass  =  '';
```

* Set `$tables` array mirror from your database:
```
$tables  = [
  'users' => ['id', 'name', 'email', 'password', 'active']
];
```

## Usage
_This version doesn't use `friedly URLs`._

* GET: `index.php?resource={table_name}`
* GET: `index.php?resource={table_name}&id={id}`

Use HTTP verbs to `create`, `update` or `delete`.

Currently, HTTP verbs allowed are: `GET`, `POST`, `PUT`, `DELETE`.
Others verbs result in `status 409`.

## Roadmap
- [x] launch v0.1
- [ ] implements PSR-4
- [ ] improve DB class
