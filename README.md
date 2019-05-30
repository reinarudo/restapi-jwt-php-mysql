Read more about jwt: https://jwt.io/introduction/

JWT Library
- https://github.com/firebase/php-jwt or just composer require 

Test tool: Postman or any other similar tool

Endpoint: /jwt-api
Client: /app

Generate token (valid for 15 mins by default)

```json
{
    "name":"generateToken",
    "param":{
        "email":"reynald.tolentino@test.com",
        "pass":"secret"
    }
}
```

Sample Response

```json
{
    "response": {
        "status": 200,
        "result": {
            "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE1NTkxMTcyMjEsImlzcyI6ImxvY2FsaG9zdCIsImV4cCI6MTU1OTExNzI4MSwidXNlcklkIjoiMSJ9.rYewZqm4d10qK6n6DrZx11XLoiOKd4jXLmN7yz9VWoo"
        }
    }
}
```

Add Customer

In order to make API requests the ff. HTTP headers must be present

- `Content-Type: application/json`
- `Authorization: Bearer eyJ0eXAiOiJKV1Qi.eyJpYXQiOjE.rYewZqm4d10qK6n`
    + only required if API name is not generateToken


```json
{
    "name":"addCustomer",
    "param":{
        "name": "John Doe",
        "email": "john@doe.com",
        "addr": "Manila",
        "mobile": "0912325555"
    }
}
```

Update Customer

```json
{
    "name":"updateCustomer",
    "param":{
        "customerId":"1",
        "name": "Foo",
        "addr": "Washington",
        "mobile": "09193234"
    }
}
```

Delete Customer


```json
{
    "name":"deleteCustomer",
    "param":{
        "customerId":"1",
    }
}
```