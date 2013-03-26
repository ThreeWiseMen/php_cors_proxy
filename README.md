PHP CORS Proxy
==============

The intent of the PHP CORS Proxy is to be a simple re-usable script you
drop onto a PHP server to avoid CORS requests in Javascript Ajax
applications.

The script will proxy requests to the origin server and deliver back
that servers responses as if it were local.

    $pcpconf = new PHPCorsProxyConfig();
    $pcpconf->addProxy("http://origin-website.com", "prefix");
    $proxy = new PHPCORSProxy($pcpconf);
    $proxy->serviceRequest();

This would set up a configuration where:

    http://main-website.com/phpcorsproxy.php/prefix/api/method/parameters?other=params

would broker to

    http://origin-website.com/api/method/parameters?other=params


