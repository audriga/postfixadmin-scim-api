# postfixadmin-scim-api

This is a [SCIM](http://www.simplecloud.info/) API for [PostfixAdmin](https://github.com/postfixadmin/postfixadmin) made by [audriga](https://www.audriga.com) and based on [scim-server-php](https://github.com/audriga/scim-server-php).

---

## Table of Contents

* [Info](#info)
* [Installation](#installation)
    * [Prerequisites](#prerequisites)
    * [Installation](#installation-1)
    * [Configuration](#configuration)
* [Authentication](#authentication)
    * [Basic Auth](#basic-auth)
    * [JWT tokens](#jwt-tokens)
        * [Usage](#usage)
        * [JWT token generation](#jwt-token-generation)
* [Authorization](#authorization)
* [SCIM clients](#scim-clients)
* [Example calls](#example-calls)
* [Acknowledgements](#acknowledgements)

---

## Info

**postfixadmin-scim-api** provides a [SCIM v2.0](https://datatracker.ietf.org/wg/scim/documents/) API to any [PostfixAdmin](https://github.com/postfixadmin/postfixadmin) setup. 

This API supports the following:

* A custom SCIM resource *Provisioning User* implementation exending the standard SCIM resource *User*
    * This resource is mapped to [PostfixAdmin](https://github.com/postfixadmin/postfixadmin) mailboxes
* A custom SCIM resource *Domain* implementation
    * This resource is mapped to [PostfixAdmin](https://github.com/postfixadmin/postfixadmin) domains
* Standard CRUD operation on above SCIM resources
* Basic auth or JWT tokens for authentication and authorization

**postfixadmin-scim-api** makes direct requests to the [PostfixAdmin](https://github.com/postfixadmin/postfixadmin) database and does not rely on the [PostfixAdmin](https://github.com/postfixadmin/postfixadmin) code. It also comes with is own http framework provided by [scim-server-php](https://github.com/audriga/scim-server-php).

This is a **work in progress** project. It already works pretty well but some features will be added in the future and some bugs may still be arround 😉

## Installation

As **postfixadmin-scim-api** is completely independent from [PostfixAdmin](https://github.com/postfixadmin/postfixadmin) installation, you don't have to install it on the same server than [PostfixAdmin](https://github.com/postfixadmin/postfixadmin). **postfixadmin-scim-api** only requires an access to the [PostfixAdmin](https://github.com/postfixadmin/postfixadmin) database.

### Prerequisites
* **postfixadmin-scim-api** requires PHP 7.4
* Dependencies are managed with [composer](https://getcomposer.org/)
* `.htaccess` and `public/.htaccess` files are provided for the [Apache HTTP Server](https://httpd.apache.org/)
    * If you are using another HTTP server software, please adapt its configuration accordingly

### Installation
* Clone the github repository in a location served by your HTTP server
* Get the dependencies with [composer](https://getcomposer.org/): `composer update`

### Configuration
* Edit the `config/config.php` file to suits your needs

## Authentication

### Basic Auth

* To use Basic Auth, send valid Basic Auth credentials with all your SCIM requests
* See example bellow

```
curl https://my.server.com/scim/Users -u "superadmin@domain.com:superpassword"
```

### JWT tokens

#### Usage

* To use a JWT token for authentication, send it as a Bearer Token 
* See example bellow

```
curl https://my.server.com/scim/Users -H "Authorization: Bearer <token>"
```

#### JWT token generation

* To generate a JWT token for a user, use the `generate_jwt.php` script located in `vendor/audriga/scim-server-php/bin/` and provided by [scim-server-php](https://github.com/audriga/scim-server-php)
* The specified secret *must* be the same secret specified in the `jwt` section of the `config/config.php` config file
* See example bellow

```
vendor/audriga/scim-server-php/bin/generate_jwt.php --username superadmin@domain.com --secret secret
```

## Authorization

* For now, only Super Admins are allowed to use the SCIM API
* Super Admins are authorized to perform all operations on all resources through the SCIM API 
* Domain Admins and regular users will get a HTTP 401 error on all operations through the SCIM API

## SCIM clients

* **postfixadmin-scim-api** was successfully tested with [Azure AD](https://learn.microsoft.com/en-us/azure/active-directory/fundamentals/sync-scim) as a SCIM client
* **postfixadmin-scim-api** should be compatible with any SCIM v2.0 client
* For a [Keycloack](https://www.keycloak.org/) client, you can have a look here: https://lab.libreho.st/libre.sh/scim/keycloak-scim

## Example calls

Example calls (null values removed for readability):

```
$ curl https://my.postfix.admin.url/Users/aaaa@bli.fr -H 'Authorization: Bearer <token>'
{
   "schemas":[
      "urn:ietf:params:scim:schemas:core:2.0:User",
      "urn:audriga:params:scim:schemas:extension:provisioning:2.0:User"
   ],
   "id":"aaaa@bli.fr",
   "meta":{
      "resourceType":"User",
      "created":"2022-05-27 12:45:08",
      "location":"https://my.postfix.admin.url/Users/aaaa@bli.fr",
      "updated":"2022-06-15 13:07:30"
   },
   "userName":"aaaa@bli.fr",
   "name":{
      "formatted":"Aaaa"
   },
   "displayName":"Aaaa",
   "active":true,
   "emails":[
        {
            "primary":true,
            "value":"aaaa@bli.fr",
        }
   "urn:ietf:params:scim:schemas:audriga:provisioning:2.0:User":{
      "sizeQuota":51200000
   }
}

$ curl https://my.postfix.admin.url/Domains/my.domain.com -H 'Authorization: Bearer <token>'
{
   "id":"my.domain.com",
   "schemas":[
      "urn:ietf:params:scim:schemas:audriga:2.0:Domain"
   ],
   "meta":{
      "resourceType":"Domain",
      "created":"2022-06-03 14:37:16",
      "updated":"2022-06-03 14:37:16",
      "location":"https://my.postfix.admin.url/Domains/my.domain.com",
   },
   "domainName":"my.domain.com",
   "description":"",
   "maxAliases":50,
   "maxMailboxes":50,
   "maxQuota":10,
   "usedQuota":2048,
   "active":true
}
```

## Acknowledgements

This software is part of the [Open Provisioning Framework](https://www.audriga.com/en/User_provisioning/Open_Provisioning_Framework) project that has received funding from the European Union's Horizon 2020 research and innovation program under grant agreement No. 871498.