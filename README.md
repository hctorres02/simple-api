# Simple-API (v0.2.1)
_A simple API (serious?)_

## Configuration:

Update `config.php` file with your database infos:
```
$host  =  'localhost';
$dbname  =  'simple-api';
$user  =  'root';
$pass  =  '';
```

> Now it is no longer necessary to inform your database schema, Simple API will read this information from the informed database using `information_schema.columns`.

> Your database schema will be stored at server session, using PHP SESSIONS. If your alter your database, needs to clean the sessions informations to application rebuild the database schema. Also required if you change anything on `config.php`.

Hide sensitive columns:
```
$excluded = [
    'password'
];
```

Bind foreign keys (JOIN):
```
$foreign = [
    'posts' => ['author_id', 'users.id'],
    'users' => ['users.id', 'author_id']
];
```

Bind columns aliases (JOIN) | (a.k.a singular)
```
$aliases = [
    'posts' => 'post',
    'users' => 'user'
];
```
## Usage

_This version doesn't use `friendly URLs`._

* GET `index.php?endpoint={host_table}`
* GET `index.php?endpoint={host_table}/{id}`
* GET `index.php?endpoint={host_table}/{id}/{foreign_table}`

Use HTTP verbs to `create`, `update` or `delete`.

Currently, HTTP verbs allowed are: `GET`, `POST`, `PUT`, `DELETE`.
Others verbs results in `status 409`.

## Roadmap
- [x] launch v0.1
- [ ] implements PSR-4
- [ ] improve DB class
