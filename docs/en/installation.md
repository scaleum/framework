[Back to Contents](./index.md)

**EN** | [UK](../uk/installation.md) | [RU](../ru/installation.md)
#  Installation

##  Requirements

- PHP 8.1+
- Composer
- Web server (Apache/Nginx)

##  Project Installation

```bash
git clone https://github.com/scaleum/framework.git
cd scaleum-framework
composer install
```

##  Server Configuration  
Apache:
```apache
<VirtualHost *:80>
    ServerName scaleum.local
    DocumentRoot /path/public
    <Directory /path/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Nginx:  
```nginx
server {
    listen 80;
    server_name scaleum.local;
    root /path/public;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```


##  Launch  
Example content of `/public/index.php`
```php
require __DIR__ . '/../vendor/autoload.php';
use Application\Base\HttpApplication;

$app = new HttpApplication([
    'application_dir' => dirname(__DIR__, 1) . '/application',
    'config_dir'      => dirname(__DIR__, 1) . '/application/config',
    'environment'     => 'dev',

    // kernel configs(for overriding)
    // 'kernel'    => [
        // expansion of definitions, will merge/override definitions from kernel->config
        // 'definitions' => [
        //     'routes.file'      => 'routes.php',
        //     'routes.directory' => 'path/to/routes',
        // ],

        // DI config files which will be loaded on bootstrap
        // file names relative to the `application_dir/config` folder or full path
        // 'configs' => [
        //     'di.kernel.php',
        // ],
    // ],
    // 'behaviors'   => [],
    // 'services'    => [],
]);

$app->bootstrap([
    // kernel configs(for overriding)
    'kernel'    => [
        // expansion of definitions, will merge/override definitions from kernel->config
        // 'definitions' => [
        //     'routes.file'      => 'routes.php',
        //     'routes.directory' => 'path/to/routes',
        // ],

        // DI config files which will be loaded on bootstrap
        // file names relative to the `application_dir/config` folder or full path
        'configs' => [
            'di.kernel.php',
        ],
    ],
    'behaviors' => [Someclass::class],
    // 'services'    => [],
]);

$app->run();
```

[Back to Contents](./index.md)