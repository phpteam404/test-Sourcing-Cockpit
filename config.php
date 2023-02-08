<?php
error_reporting(0);
ini_set('display_errors', 0);
$currentCookieParams = session_get_cookie_params();
session_set_cookie_params(
    $currentCookieParams["lifetime"],
    $currentCookieParams["path"],
    $currentCookieParams['domain'],
    true,
    $currentCookieParams["httponly"]
);
define('ENV','DEV');
switch(ENV)
{
    case 'DEV':
        $base_host = sprintf('%s://%s/',$_SERVER['SERVER_PORT'] == 443 ? 'https' : 'http',$_SERVER['SERVER_NAME'])."sourcing-cockpit/";
        /* site urls start */
        define('WEB_BASE_URL', $base_host.'html/');
        define('REST_API_URL', $base_host.'rest/');
        define('PAGING_LIMIT', '10');
        /* site urls ends */

        /*database configuration starts*/ //

        // define('DB_HOST', 'ZFZYblhnb1d3a3JGNDBYWnRZSTNlQT09'); //3.109.23.34
        define('DB_HOST', 'cEd6Q3V0a2Z0RDR0amJ3UE8vUEJJdz09'); //3.110.163.202
        define('DB_USERNAME', 'MjBoZjJ6MGhoOUg1eHpaOCs0cDFuUT09'); //admin
        define('DB_PASSWORD', 'VzBBdW5qRU43aXQ4UXZUdUtZSUd6QT09'); //the@123
        define('DB_NAME', 'OXlrYmR6b1ZiTVlaaDJWblowTjRwZz09');//scpdev

     
        // define('DB_HOST', 'TFdBUG9iYUp1eTBFdmMzNGdENTgyd2RhR21iakVkTXNUb1VZb1NvcGowK3phWDNBZXNwWVVHQ241NlRHK3V1ZFV1K0VBVE9ldVg2RHdUeGg5cUYya2c9PQ==');
        // define('DB_USERNAME', 'c1dzREtSMWNMSkFCM2FySlNBcmkrQT09');
        // define('DB_PASSWORD', 'N3crNy9Bd3loS3ZjaVlRWUYyR0lPZ1o5S2xIbUI4OXRGdHhRQTFMNkoyND0');
  	    // define('DB_NAME', 'TWluZC9td1hFc1FiNkcvemJPSkZkdz09');

        /* database configuration ends*/
        define("SITE_ACCESS_TOKEN_EXPIRY","18000");
        
        /* Image sizes */
        define('SMALL_IMAGE','70x33');
        define('MEDIUM_IMAGE','500x500');
        /* Image sizes */

        /*mongo server urls starts*/
        define('MONGO_SERVICE_URL', 'http://183.82.97.231:9085/');
        define('LOG_AUTH_KEY', 'F%DTBh*nY9Kq@QdWc');
        /*mongo server urls ends*/

        /* aes encryption configuration starts */
        //define('AES_KEY', 'nwXcTJVzFDQIEpKWSO88m73ElDJFJ1a5YJVWDYsG');//old
        define('AES_KEY', 'JKj178jircAPx7h4CbGyYVV6u0A1JF7YN5GfWDWx');
        define('DATA_ENCRYPT',FALSE);
        /* aes encryption configuration ends */

        define('EXCEL_UPLOAD_SIZE','10485760');
        //define('IMAGE_UPLOAD_SIZE','10485760');
        define('IMAGE_UPLOAD_SIZE','100000000');//100MB
        //File system path
        //define('FILE_SYSTEM_PATH','/var/www/app_files/');
        define('FILE_SYSTEM_PATH','');
        define('MAX_INVALID_PASSWORD_ATTEMPTS',5);

        define('SMTP_MAIL_FROM','app.mazic@gmail.com');
        define('SMTP_MAIL_PASSWORD','app_mazic.');
        define('SMTP_MAIL_NOREPLY','app.mazic@gmail.com');

        define('SEND_GRID_API_KEY', 'U0cuVms2Z1laSjdSdXlkUHU0OFhPVjJJUS4yZDgxNG12UW9SeTFWVjFCSlF0Y0xpX2NzWVE5LWd5SGY5dkxFbDNGakhN');//new code
        define('SEND_GRID_FROM_EMAIL', 'app.mazic@gmail.com');
        define('SEND_GRID_FROM_NAME', 'Sourcing Cockpit');

                    define('EMAIL_HEADER_CONTENT', "<!doctype html>
<html>
<head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
<title>Email Template</title>
</head>
<body style=\"padding:0;margin:0; font-family:arial;\">
<table style=\"width: 100%;text-align: left;\" cellpadding=\"0\" border=\"0\" cellspacing=\"0\">
<tbody>
<tr>
<td><img src=\"{logo}\" alt=\"banner\" style=\"width: 100px;\"></td>
</tr>
<tr>
<td colspan=\"2\"  style=\"opacity:0.4;\"><hr color=\"#e04826;\" size=\"1\"></td>        </tr>
<tr><td style=\"font-size: 12px;text-align: left;padding-left:10px;\">");
                    define('EMAIL_FOOTER_CONTENT', "</td></tr>

<tr><td colspan=\"2\" style=\"opacity:0.4;\"><hr color=\"#e04826;\" size=\"1\"></td>
</tr><tr><td colspan=\"2\" style=\"font-size: 11px;color: #757575;float: left;padding-left:10px;\"><i></i><p style=\"color: #757575;\">© Copyright 2017<br>with BVBA (HQ)Jan Van Rijswijcklaan 135<br>2018 Antwerp Belgium<br>Parking: Nationale Bank<br>+32 (0)477 77 25 12</p></td></tr></tbody></table>
</body>
</html>");
        define('PUBLIC_API_EMAIL_ID', 'volen.davidov@with-services.com');
        define('SAML_LOGIN', 'https://dev.sourcingcockpit.com/sprint82/');
        define('SAML_LOGOUT', 'https://dev.sourcingcockpit.com/sprint82/www/module.php/core/authenticate.php?as=default-sp&logout');
        define('CONTRACT_BUILDER_API_BASE_URL', 'https://139.59.146.235/api/');
        define('CONTRACT_BUILDER_API_AUTH_TOKEN', 'g4xFMN8Dyhcy3oERzLNyx8eQ6mTeMcJcKVoVgxAeNKtfYUfDwf9qZdLkowXtfQT9yfjHXnvrcguLaMTmBXBJKHC7E9di4QLaQVW4');
        define('NO_OF_TAGS', 72);
        

        break;
}

?>
