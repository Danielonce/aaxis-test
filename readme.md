Aaxis Symfony test:

Versions:
	- PHP: 8.3.2
	- Composer: 2.6.6
	- Postgres: 14.10
	- Symfony: 6.4

1- Open a terminal and run `composer install`

2- Run `php bin/console d:d:c` to create a new database.

3- Run `php bin/console d:s:c` to create the database schema.

4- Run `php bin/console d:f:l` to load the fixtures.

5- Run `symfony server:start` to start the server.

    Copy your localhost route
    Paste it on Postman or your favourite API platform client.

6- Open Postman and access to the route `/api/auth/register` (POST) to create a new user.

<ins>Registering a single user:</ins>
![Project Preview](/images/step1.png)

7- In `/api/auth/login` (POST), get the token to authenticate the user you recently created.

<ins>Login:</ins>
![Project Preview](/images/step2.png)

8- Now you can access to all the API endpoints, just by passing the token on "Bearer Token" on the "Authorization" tab.

<ins>If not authenticated:</ins>
![Project Preview](/images/step3.png)

<ins>Retrieving a list of products in /api/product once the user is authenticated:</ins>
![Project Preview](/images/authorized1.png)

9- Try the rest of the endpoints:

<ins>/api/product/create</ins>
![Project Preview](/images/authorized2.png)

<ins>/api/product/update</ins>
![Project Preview](/images/authorized3.png)



