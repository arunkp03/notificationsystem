# Task Notification API (Laravel 11)

This project implements a task assignment notification system for a project management app.

## Implemented Features

- Create task: `POST /api/tasks`
- Auto notification on assignment through event + queued listener
- List current user notifications: `GET /api/notifications`
- Mark notification as read: `POST /api/notifications/{id}/read`
- Form Request validation, Eloquent relationships, migrations with FKs
- Pagination for notifications
- API Resources for clean response payloads
- Feature tests for core flows

## Tech Notes

- Laravel: 11
- Queue driver: `database` (`QUEUE_CONNECTION=database`)
- Database: `mysql` (configured via `.env`)
- Auth for API examples: `X-User-Id` request header

## Architecture

- `TaskController@store` creates task and dispatches `TaskAssigned`
- `TaskAssigned` event carries `task` and `assignedBy`
- `CreateTaskAssignmentNotification` listener implements `ShouldQueue`
- Listener creates a `notifications` table record asynchronously

## Local Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
```

## Nginx Configuration (MAC Specific)
#### To be modified based on environment and directory structure.

Create file: `/opt/homebrew/etc/nginx/servers/assignment.conf`

```nginx
server {
    listen 8080;
    server_name localhost;
    root /Users/username/Projects/Assignment/public;
    index index.php index.html;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT $realpath_root;
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Apply and validate:

```bash
brew services restart php@8.2
brew services restart nginx
nginx -t
nginx -s reload
```

## Run API and Queue Worker

Use separate terminals:

```bash
php artisan queue:work
```

## Sample API Calls

Create task (assignee user id 2, assigner user id 1):

```bash
curl -X POST http://localhost:8080/api/tasks \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "X-User-Id: 1" \
  -d '{"title":"Build API","description":"Create REST endpoints","assigned_to":2,"status":"pending"}'
```

List notifications for user 2:

```bash
curl -X GET http://localhost:8080/api/notifications \
  -H "Accept: application/json" \
  -H "X-User-Id: 2"
```

Mark notification as read:

```bash
curl -X POST http://localhost:8080/api/notifications/1/read \
  -H "Accept: application/json" \
  -H "X-User-Id: 2"
```

## API Documentation

### Base URL

- `http://localhost:8080`

### Authentication

- Pass authenticated user id using header: `X-User-Id`

### 1) Create Task

- **Method:** `POST`
- **URL:** `/api/tasks`
- **Headers:**
  - `Accept: application/json`
  - `Content-Type: application/json`
  - `X-User-Id: 1`
- **Body:**

```json
{
  "title": "Build API",
  "description": "Create REST endpoints",
  "assigned_to": 2,
  "status": "pending"
}
```

- **Success Response:** `201 Created`

```json
{
  "message": "Task created successfully.",
  "data": {
    "id": 1,
    "title": "Build API",
    "description": "Create REST endpoints",
    "assigned_to": 2,
    "status": "pending",
    "created_at": "2026-04-08T08:00:00.000000Z",
    "updated_at": "2026-04-08T08:00:00.000000Z"
  }
}
```

### 2) Get Notifications

- **Method:** `GET`
- **URL:** `/api/notifications`
- **Headers:**
  - `Accept: application/json`
  - `X-User-Id: 2`
- **Success Response:** `200 OK` (paginated)

```json
{
  "data": [
    {
      "id": 1,
      "user_id": 2,
      "type": "task_assigned",
      "message": "User John Manager assigned you task: Build API",
      "read_at": null,
      "created_at": "2026-04-08T08:00:01.000000Z"
    }
  ]
}
```

### 3) Mark Notification as Read

- **Method:** `POST`
- **URL:** `/api/notifications/{id}/read`
- **Headers:**
  - `Accept: application/json`
  - `X-User-Id: 2`
- **Success Response:** `200 OK`

```json
{
  "message": "Notification marked as read.",
  "data": {
    "id": 1,
    "user_id": 2,
    "type": "task_assigned",
    "message": "User John Manager assigned you task: Build API",
    "read_at": "2026-04-08T08:01:00.000000Z",
    "created_at": "2026-04-08T08:00:01.000000Z"
  }
}
```

### Error Responses

- `401 Unauthorized`

```json
{
  "message": "Unauthenticated. Provide X-User-Id header."
}
```

- `404 Not Found`

```json
{
  "message": "Notification not found."
}
```

- `422 Unprocessable Entity` (validation)

```json
{
  "message": "The title field is required.",
  "errors": {
    "title": [
      "The title field is required."
    ]
  }
}
```

## Tests

```bash
php artisan test
```

