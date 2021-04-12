# Simple-API (v0.7)
_A simple API (serious?)_

## Roadmap
- [x] first launch!
- [x] implements PSR-4
- [ ] live demo
- [ ] improve DB class
- - [x] `join`
- - [ ] `like` *(a.k.a. dataset filter)*
- - [ ] `columns selection`
- - [ ] `offset/limit` _(a.k.a. pagination)_
- - [ ] `order by` 

## Configuration

##### Update `.env` file with your database infos:
```
host = localhost
dbname = simple-api
user = root
pass =
```

> Simple API will read this information from the informed database using `information_schema.columns`. The database schema will be stored at server session, using `PHP SESSIONS`.

##### Hide sensitive columns:
```
[excluded]
0=password
```

##### Bind columns aliases (JOIN) | _(a.k.a. singular)_
```
[aliases]
posts = post
users = user
```

> If your alter your database, needs to clean the sessions informations to application rebuild the database schema. Also required if you change anything on `.env`.

## Usage

_This version __doesn't__ use `friendly URLs`._

* GET `index.php?table={tb_name}`
* GET `index.php?table={tb_name}&id={id}`
* GET `index.php?table={tb_name}&id={id}&join={tb_name}`

Use HTTP verbs to `create`, `update` or `delete`.

Currently, HTTP verbs allowed are: `GET`, `POST`, `PUT`, `DELETE`. Others verbs results in `status 405`.