Rename .env.example to .env and fill connection to mongoDB: MONGODB_URI
and create schema: php bin/console doctrine:mongodb:schema:create

setup transports: php bin/console messenger:setup-transports

starts server: symfony server:start -d OR php -S 127.0.0.1:8000 -t public

starts messenger: php bin/console messenger:consume async -vv

to run tests: php bin/phpunit

api documentation: http://127.0.0.1:8000/api/doc

endpoints:
- POST /api/users - create user
- GET /api/users - list users

Example create user params:
{
    "firstName": "John",
    "lastName": "Doe",
    "phoneNumbers": ["+380971234567", "+380501112233"]
}
