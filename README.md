# Forum microservice

Web application for project `BPxxxx project name`

- [Requirements](#markdown-header-requirements)
- [Development environment setup](#markdown-header-development-setup)
    - [Docker setup](#markdown-header-docker-setup)
    - [Starting the dev environment](#markdown-header-starting-the-dev-environment)
    - [Cleaning up](#markdown-header-cleaning-up)
    - [Database](#markdown-header-database)
    - [Configuration](#markdown-header-configuration)
    - [Rebuilding the containers](#markdown-header-rebuilding-the-containers)
- [API Endpoints](#markdown-header-api-endpoints)
    - [Conventions](#markdown-header-conventions)
    - [Topic endpoints](#markdown-header-topic-endpoints)
    - [Post endpoints](#markdown-header-post-endpoints)
    - [Reply endpoints](#markdown-header-reply-endpoints)

## Requirements

* VMWare Fusion and Homebrew + docker-machine + docker-compose
* A valid API token - this is supposed to come from your sign-in service, but for development you can run `bin/console app:jwt <username>`

## Development setup

### Docker setup

If you haven't done this before for another project:

* `brew update && brew install docker-machine docker-compose`
* `docker-machine create dev --driver vmwarefusion` (or virtualbox or aws or whatever)
* `docker-machine ip dev` will give you the IP address to add to `/etc/hosts` to point at, e.g. `forum.dev`
* `eval "$(docker-machine env dev)"`

Otherwise it's just:

* `docker-machine start dev`
* `eval "$(docker-machine env dev)"`
* Send your API calls to https://forum.dev:9443/forum/ -- note the port number. A sample [Paw](https://luckymarmot.com/paw/) file is included, just change the token and you can test the endpoints and generate code for your app.

### Starting the dev environment

* `docker-compose up`

The first time you do this, it'll download the `forum:develop` docker image, which will take a few minutes, then build the development box on top of it, which pretty much just backs up and resets the database on startup. After that it should be quicker.

You should see something like this at the end of pages of crap: 
```
app_1      | 2015-11-25 15:22:49,590 INFO success: fpm entered RUNNING state, process has stayed up for > than 1 seconds (startsecs)
app_1      | 2015-11-25 15:22:49,591 INFO success: nginx entered RUNNING state, process has stayed up for > than 1 seconds (startsecs)
```

Like "grunt serve", this will stay up until you hit Ctrl-C, then it'll shut down. Alternatively you can run `docker-compose up -d` to make it run in the background, then run `docker-compose stop` when you're done. If you do that, you won't see any messages on screen if processes in the containers fail.

If you need to access the docker tools or server in new terminal tabs/windows, just run `eval "$(docker-machine env dev)"`. You can test if it worked by running `docker ps`.


### Cleaning up

* `docker-machine stop dev` -- shut down the docker VM if you need the CPU & memory back.

#### *Really* cleaning up

Docker containers are instances of docker images. Over time these can build up to take up a lot of space. You can run `docker-compose rm` to remove your container instances, but the disk images will remain.

You can view all past container instances by running `docker ps -a` and remove them individually with `docker rm <name>`. To go all-out and delete all instances, run `docker rm $(docker ps -aq)`.

Docker images can be listed with `docker images`. Remove images with `docker rmi <image id>` or all images with `docker rmi $(docker images -q)` -- but if you do this, the next time you build will take a long time and a lot of downloading!


### Database

The dev environment is reset every time the dev container starts. That means it gets a new SSL key and the database and search engine are reset. The previous database is saved in `db-previous.sql` just in case there was something there you wanted. If you want to maintain database content between builds, then update `var/initialdb.sql`:

```sh
docker exec -it forum_db_1 mysqldump -u root -phairnet forum > db.sql
```

To edit or query the database manually:

```sh
docker exec -it forum_db_1 mysql -u root -phairnet forum
```

The initial db contains a test dataset in the `test` namespace, and a number of test users. You can rebuild these from the initialdb file by just restarting the container, or reset the database while it's running by running `docker exec -ti forum_app_1 bin/console haute:fix:load`.

### Configuration

Generally all runtime configuration is held in environment variables. Unlike in regular Symfony projects, environment variables will override configuration variables when set. So `docker-compose.yml` overrides `docker/base/Dockerfile`, which overrides `app/config/parameters.yml`. 

### Rebuilding the containers

Docker Hub can automatically rebuild the base container whenever you push to bitbucket/github. It takes about 10-12 minutes to rebuild the boxes, but you probably don't need to worry about this unless you've changed the docker environment.

If you do need to rebuild your dev box because the base container has changed:

* `docker pull tuimedia/forum:develop`
* `docker-compose rm -f app && docker-compose build app`

If you don't have access to our automated build (and if you're not at Tui it's unlikely!) then you can set up your own, or just build it locally yourself:

`docker build -t tuimedia/forum:develop .`

## API Endpoints

### Conventions

All endpoints begin with a forum namespace - this separates one forum from another. All namespaces share the same users - if you don't want this, create a separate instance of the API.

Users are created on demand, provided the JWT is valid. Profile information should be retrieved from the profile service - all the forum stores about a user is the username provided in the JWT.

Almost all endpoints allow you to provide an `?include=` URL parameter which will expand embedded objects. Available includes are listed for each endpoint, but these can also be nested up to 3 levels, for example: `/{namespace}/topics?include=owner,posts.replies`.

Pagination works through the `page=` and `limit=` URL parameters. Default limit is 10 and the first page is 1 (not 0). Result sets will contain a `meta` section with the current page, the total number of results, and links to the next and previous pages, e.g.:

```json
{
  "data": [
  ],
  "meta": {
    "page": 2,
    "count": 100,
    "nextPage": 3,
    "previousPage": 1,
    "next": "https://forum.dev:9443/forum/test/topics/?limit=10&page=3",
    "previous": "https://forum.dev:9443/forum/test/topics/?limit=10&page=1"
  }
}
```

All results from the API are returned in this format (except the `meta` section is missing when there is no metadata). The `data` section will either be a single object or an array of objects. 

The service will return the following HTTP response codes:

* `200 OK` - standard response to `GET` requests and edits
* `201 Created` - when creating objects
* `204 No content` - usually on `DELETE` requests
* `422 Unprocessable entity` - validation errors, invalid data
* `500` - application error

### Display names

The forum service doesn't store profiles at all, only the user name provided in the JWT. However every endpoint that returns one or more `createdBy` properties can also retrieve the user's display name from the SSO service. To do this, add `displayName` to the `includes` parameter. For example:

`GET /test/topics/?include=displayName`

```json
{
  "data": [
    {
      "id": "075a8406-b52e-11e5-b52e-0242ac110002",
      "created": "2015-12-31T10:46:51+00:00",
      "updated": "2015-12-31T10:46:51+00:00",
      "namespace": "test",
      "title": "Qui doloremque aperiam qui rerum accusamus beatae.",
      "externalReference": null,
      "createdBy": "jvonrueden",
      "displayName": "jvonrueden",
      "parentId": null
    }
    …
  ]
}
```

### Topic endpoints

#### `GET /{namespace}/topics/?reference={url}&include={includes}&parent={parent}`

Return a paginated list of topics in the given namespace. Results are presented most-recent-first.

If a reference is provided, results are filtered by that external reference. External references can be anything but should be URLs; they're provided as a way to find individual topics, for example to embed a discussion on a particular page without knowing the topic UUID. However external references do not have to be unique, and this endpoint will return all matching topics, so it's best to store the UUID of the topic when creating it.

Available includes:

* `children`
* `parent`
* `posts`

#### `GET /{namespace}/topics/{id}?include={includes}`

Return the requested topic in the given namespace. 

Available includes:

* `children`
* `parent`
* `posts`


#### `POST /{namespace}/topics/`

Create a new topic.

Available includes:

* `children`
* `parent`
* `posts`

Request body should be a JSON object as follows:

```json
{
  "title": "Et beatae totam nulla quisquam.",
  "externalReference": "http://www.trantow.com/voluptates-unde-optio-accusamus",
  "parentId": null
}
```

Only `title` is required. Namespace is taken from the URL and `externalReference` and `parentId` are both optional.

#### `DELETE /{namespace}/topics/{id}`

Remove the requested topic in the given namespace. Returns an empty response with the status `204 No content`.


### Post endpoints

#### `GET /{namespace}/posts/?topic={topicId}&include={includes}&limit={limit}&page={page}`

Return a paginated list of posts in the given namespace. Results are ordered by sticky status, then most-recent-first.

If a topic is provided, results are limited to that topic id.

Available includes:

* `topic`
* `replies`

#### `GET /{namespace}/posts/{id}?include={includes}`

Return the requested post in the given namespace. 

Available includes:

* `topic`
* `replies`


#### `POST /{namespace}/posts/`

Create a new post.

Available includes:

* `topic`
* `replies` (pointless)

Request body should be a JSON object as follows:

```json
{
  "content": "Et beatae totam nulla quisquam.",
  "topic": "075b606b-b52e-11e5-b52e-0242ac110002",
  "isSticky": false
}
```

`content` and `topic` are required. Namespace is taken from the topic id, and stickiness is optional.

#### `DELETE /{namespace}/posts/{id}`

Remove the requested topic in the given namespace. Returns an empty response with the status `204 No content`.


#### `POST /{namespace}/posts/{id}/ratings?include={includes}`

Rate the given post

Available includes:

* `topic`
* `replies`

Request body should be a JSON object as follows:

```json
{
  "score": 1,
}
```

Score must be either `-1` or `1`.

#### `POST /{namespace}/posts/{id}/replies?include={includes}`

Add a reply to the given post

Available includes:

* `post`

Request body should be a JSON object as follows:

```json
{
  "score": 1,
}
```

Score must be either `-1` or `1`.


### Reply endpoints

#### `GET /{namespace}/replies/?topic={topicId}&include={includes}&limit={limit}&page={page}`

Return a paginated list of replies in the given namespace. Results are ordered most-recent-first.

If a post id is provided, results are limited to that post.

Available includes:

* `post`


#### `POST /{namespace}/replies/?include={includes}`

Reply to a post.

Available includes:

* `post`

Request body should be a JSON object as follows:

```json
{
  "content": "Et beatae totam nulla quisquam.",
  "post": "075b606b-b52e-11e5-b52e-0242ac110002"
}
```

`content` and `post` are both required.


#### `DELETE /{namespace}/replies/{id}`

Remove the requested reply in the given namespace. Returns an empty response with the status `204 No content`.

