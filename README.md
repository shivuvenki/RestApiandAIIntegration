RESTful API for Book Management

Overview

This project implements a RESTful API for managing a collection of books.

It includes the following features:

Book Management: CRUD operations for books (Create, Read, Update, Delete).

Authentication: Token-based authentication using JSON Web Tokens (JWT).

AI Integration: An endpoint to generate AI-generated book summaries.

Setup Instructions

Prerequisites

1.PHP version 7.4 or higher.

2. Paste your applictaion folder inside xampp/htdocs

3.Start the PHP built-in server

4.Need Composer install for managing dependencies

For JWT Library we need to run command in our application command prompt-

composer require firebase/php-jwt


JWT Expiry:

Tokens expire after 1 hour, and you can extend this by modifying the exp value in the payload.


1. Create database in phpmyadmin and import database in phpmyadmin database availabe inside database folder(spaceassignment.sql) file

Endpoints for registration:

2.Create a user registration 

POST http://localhost/assignmentFinal/register_login.php/register

inside Body - raw section

{
  "name":"test",
  "email":"test@gmail.com",
  "password":12345678

}

3. Registered User can login to get access token

POST http://localhost/assignmentFinal/register_login.php/auth

inside Body - raw section

{
  "email":"test@gmail.com",
  "password":12345678

}
Response:

{
  "token": "<your_jwt_token>"
}

Use the token in the Authorization header (e.g., Bearer <token>) for accessing Books API endpoints.
requests:

Authorization: Bearer <your_jwt_token>

4.Endpoints For Books Management:

I) GET /api/books: Retrieve all books. (http://localhost/assignmentFinal/index.php/api/books)

  Response Like:
   [
    {
        "id": 1,
        "title": "1984",
        "author": "George Orwell",
        "published_year": 1949
    },
    {
        "id": 2,
        "title": "To Kill a Mockingbird",
        "author": "Harper Lee",
        "published_year": 1960
    },
	................
  ]
II) GET /api/books/{id}: Retrieve a single book by its ID. (http://localhost/assignmentFinal/index.php/api/books/1)

	Response Like:
   [
    {
        "id": 1,
        "title": "1984",
        "author": "George Orwell",
        "published_year": 1949
    }
  ]

III) POST /api/books: Add a new book. (http://localhost/assignmentFinal/index.php/api/books)

	inside Body - raw section
	{
		"title":"test",
		"author":"test",
		"published_year":"2025"

	}

IV) PUT /api/books/{id}: Update an existing book. (http://localhost/assignmentFinal/index.php/api/books/1)

	inside Body - raw section
	{
		"title":"test",
		"author":"test",
		"published_year":"2025"

	}

V) DELETE /api/books/{id}: Delete a book. (http://localhost/assignmentFinal/index.php/api/books/1)



5. AI Integration for generate book summary(Using Hugging Face)

To get an access token from Hugging Face, follow these steps:

1. Create a Hugging Face Account (if you don't already have one):
    -> Go to the Hugging Face website: https://huggingface.co/.
    -> Click on Sign Up or Log In if you already have an account.

2. Go to Your Settings Page:
    -> After logging in, click on your profile picture (top-right corner).
    -> Select Settings from the dropdown menu.

3. Navigate to the Access Tokens Section:
    -> On the settings page, look for Access Tokens in the left-hand menu and click on it.

4. Generate a New Access Token:
    -> In the Access Tokens section, click the New Token button.
    -> Provide a name for your token (e.g., "My API Token").
    -> Select the Scope (level of access) for the token:
    -> Read: For accessing public models, datasets, or Spaces.
    -> Write: For uploading or updating models, datasets, or Spaces.
    -> Admin: For full access to your Hugging Face account.
    -> Click Generate Token.
5. Copy the Access Token:
	-> Once the token is generated, copy it and keep it in a secure place. You won't be able to view the token again.
	-> If you lose it, you can revoke and regenerate a new one.
6. Use the Access Token:
	File - index.php 
	$hugging_face_api_key store in tis variable
	
Endpoint for generate Book Summary:

POST /api/books/generate-summary  (http://localhost/assignmentFinal/index.php/api/books/generate-summary)
	
	inside Body - raw section
	{
		"book_id":"14"
	}


 


