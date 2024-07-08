# Resonance Project

To start the project you need to:
1. Install dependencies with `composer install`
2. Create `config.ini` (you can copy `config.ini.example`)
3. Run `php bin/resonance.php serve` in the terminal to start the server

## Using SSL

In order to use SSL you need to [generate SSL certificate for a local development](https://resonance.distantmagic.com/docs/extras/ssl-certificate-for-local-development.html)
and uncomment SSL related settings in `app/Command/Serve.php`.
