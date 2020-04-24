# ONLYOFFICE blog


### Features

- Write, view and search blog posts.
- Sing up, Login. Session is based on cookie.
- Collect page views and search logs into Elasticsearch via fluentd in Real-Time.

### LICENSE

GNU GENERAL PUBLIC LICENSE

### How to start with XAMPP

1. Download xampp https://www.apachefriends.org/ru/download.html
2. Run xampp-control.exe
3. Install Apache and MySQL, run it
4. Go to admin panel MySQL (button "admin") If has errors 
   enter your details for connecting to mysql in file config.inc.php. Example path - C:\xampp\phpMyAdmin\config.inc.php:
   
   $cfg['Servers'][$i]['user'] = 'root';
   $cfg['Servers'][$i]['password'] = 'root';
   $cfg['Servers'][$i]['controluser'] = 'root';
   $cfg['Servers'][$i]['controlpass'] = 'root';   )
 5. Import sql file
 6. In file wp-config.php - enter your details for connecting to base
 7. Go to localhost/wp-admin => login - root; pass - root (default)
