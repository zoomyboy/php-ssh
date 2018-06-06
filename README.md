# PHP SSH Wrapper around pphseclib for Laravel

## Check login
You can check if ssh credentials are valid - either with Password authentication or with PubKe authentication

use Zoomyboy\PhpSsh\Client;

$sshValid = Client::auth($host, $user)
    ->withPassword($password)
    ->check();
$sshValid = Client::auth($host, $user)
    ->withKeyFile($keyFile)
    ->check();

The Key file has to be an absolute path to your private key filelogin.

If you really want to connect, you can use the connect method:
$sshValid = Client::auth($host, $user)
    ->withKeyFile($keyFile)
    ->connect();
