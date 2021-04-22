# Simple-API (v0.9)
Build automatically an API for your database, without the need to configure models, routes and other things that are requested.

## Roadmap
- [x] first launch!
- [x] implements PSR-4
- [ ] implements friendly URLs
  - [x]  Apache
  - [x] IIS
  - [ ] Nginx
- [x] live demo
- [ ] improve DB class
  - [x] `join`
  - [x] `columns selection`
  - [x] `order by` 
  - [ ] `like` _(a.k.a. dataset filter)_
  - [ ] `offset/limit` _(a.k.a. pagination)_

## Live demo
[Run live demo](#usage)

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
0 = password
```

##### Bind columns aliases (JOIN) | _(a.k.a. singular)_
```
[aliases]
posts = post
users = user
```

> If your alter your database, needs to clean the sessions informations to application rebuild the database schema. Also required if you change anything on `.env`.

## Usage

##### single table
- GET `/{tb_name}` [•](https://hctorres02.gear.host/simple-api/users)

##### single table with id
- GET `/{tb_name}/{id}` [•](https://hctorres02.gear.host/simple-api/users/1)

##### join tables with id
- GET `/{tb_name}/{id}/{join_tb_name}` [•](https://hctorres02.gear.host/simple-api/users/1/posts)

Use HTTP verbs to `create`, `update` or `delete`.

> Currently, HTTP verbs allowed are: `GET`, `POST`, `PUT`, `DELETE`. Others verbs results in `status 405`.

### Columns selection (comma separated)
##### single table
- GET `/{tb_name}?columns={columns}` [•](https://hctorres02.gear.host/simple-api/users?columns=id,name)

##### single table with id
- GET `/{tb_name}/{id}?columns={columns}` [•](https://hctorres02.gear.host/simple-api/users/1/?columns=id,name,email)

##### multi tables with id
- ###### array style (recomended)
  GET `/{tb_name}/{id}/{join_tb_name}?columns[{tb_name}]={columns}&columns[{tb_name}]={columns}` [•](https://hctorres02.gear.host/simple-api/users/1/posts?columns[users]=id,name,email&columns[posts]=title,body)	

- ###### comma style
  GET `/{tb_name}/{id}/{join_tb_name}?columns={tb_name}.{column_name}` [•](https://hctorres02.gear.host/simple-api/users/1/posts?columns=users.id,users.name,users.email,posts.title,posts.body)
