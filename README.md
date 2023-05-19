# Book It App
This is a demo Restful API that individuals can use to order books from an imaginary store called "Book It" using various endpoints. Payments can then be made for these books using WiPay.

## Installation
- Copy the .env.example file `cp .env.example .env` and fill in your database credentials
- Go to the root of the app and run `composer install` to add all the dependencies needed for the app to work
- Now we are going to need to seed our database with a demo user (member@example.com) and an admin user (admin@example.com). The password for both accounts is `password`
- Now the app should be up and running. To see API documentation you can go to `localhost:8000/request-docs` or this link to the [Postman Collection](https://www.postman.com/niageo-technologies/workspace/book-it/collection/2596102-0c1bb811-7710-4a8b-9cf3-ae052902643c?action=share&creator=2596102)
