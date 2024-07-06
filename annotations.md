# Manual de configurações

Anotações sobre as coisas que serão preciso fazer para o funcionamento do projeto em vista que não dei commit das configurações.

## Plugins

### JWT Authentication for WP REST API

Fazer o download e a configuração do plugin.

[Download e Documentação](https://br.wordpress.org/plugins/jwt-authentication-for-wp-rest-api/#description)

#### Exemplo de curl

```bash
curl --request GET \
  --url http://localhost:8080/wp-json/api/v1/users \
  --header 'Authorization: Bearer eyJ0eX...' \
  --header 'host: localhost:8080'
```

Na documentação manda fazer algumas configurações mas não diz exatamente onde dever ser colado os trechos de códigos.

#### .htaccess

```text
<IfModule mod_rewrite.c>
...
RewriteCond %{HTTP:Authorization} ^(.*)
RewriteRule ^(.*) - [E=HTTP_AUTHORIZATION:%1]
</IfModule>
SetEnvIf Authorization "(.*)" HTTP_AUTHORIZATION=$1
```

> Cole as linhas sobre o fechamento do ifModule e a ultima abaixo como no exemplo.

#### wp-config.php

```php
/* JWT Authentication for WP REST API  config*/
define('JWT_AUTH_SECRET_KEY', getenv_docker('JWT_AUTH_SECRET_KEY', 'you-secret) );

define('JWT_AUTH_CORS_ENABLE', true);
```

> Criei uma configuração para passar env via docker ou usar valor padrão.

https://stackoverflow.com/questions/45413268/integrating-wordpress-authorization-and-sign-in-with-android

> As duas linhas devem ser coladas abaixo  das config:
> define('AUTH_KEY','');
>
> define('SECURE_AUTH_KEY',  '');
>
> define('LOGGED_IN_KEY',    '');
>
> define('NONCE_KEY',        '');
>
> define('AUTH_SALT',        '');
>
> define('SECURE_AUTH_SALT', '');
>
> define('LOGGED_IN_SALT',   '');
>
> define('NONCE_SALT',       '');
