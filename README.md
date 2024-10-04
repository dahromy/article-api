# Article API

This project is a RESTful API for managing articles using Symfony 6.4, PHP 8.2, and MongoDB.

## Requirements

- PHP 8.2
- Composer
- MongoDB

## Installation

1. Clone the repository:
   ```
   git clone https://github.com/dahromy/article-api.git
   ```
    ```
    cd article-api
    ```

2. Install dependencies:
    ```
    composer install
    ```
3. Configure your MongoDB connection in the `.env` file:
    ```
   MONGODB_URL=mongodb://localhost:27017
   MONGODB_DB=poc
   ```
4. Generate JWT keys:
   ```
   php bin/console lexik:jwt:generate-keypair
   ```
5. Create a user:
   ```
   php bin/console app:create-user email@example.com password
    ```


## Usage

1. Start the Symfony server:
    ```
    symfony server:start
    ```
2. Access the API documentation at `http://localhost:8000/api/doc`

3. To authenticate, send a POST request to `/api/login_check` with the following body:
```json
{
  "username": "email@example.com",
  "password": "password"
}
```

Use the returned token in the Authorization header for subsequent requests.

## API Endpoints

- GET ``/api/articles`` - List all articles
- POST ``/api/articles ``- Create a new article
- GET ``/api/articles/{id}`` - Get a specific article
- PUT ``/api/articles/{id}`` - Update an article
- DELETE ``/api/articles/{id}`` - Delete an article