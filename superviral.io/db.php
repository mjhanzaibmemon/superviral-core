<?php
use Aws\CloudWatch\CloudWatchClient;

////////////////// ABOVE ALL IS IP RESCTRICTER

// Detect the subdomain dynamically
$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.superviral.io)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/' . $subdomain . '/etra.group';
if (!empty($initial) && $initial != "superviral.") {
    $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;
}

$blacklist_limit = 7;

global $sesusernamev2;
global $sespasswordv2;
global $cloudwatchkey;
global $cloudwatchpassword;
global $rapidapihost;
global $rapidapikey;

require __DIR__ . '/../etra.group/loadParamStore.php';

// -------------------------
// DB configuration for EKS / RDS
// -------------------------
// Prefer environment variables (EKS best practice).
// Fall back to values that may have been set by loadParamStore.php
// for backward compatibility with the old EC2 setup.
// $dbHost = getenv('DB_HOST') ?: ($dbHost ?? '127.0.0.1');          // RDS endpoint on EKS
// $dbName = getenv('DB_NAME') ?: ($dbName ?? 'etra_superviral');    // default DB name
// $dbUser = getenv('DB_USER') ?: ($dbUser ?? null);
// $dbPass = getenv('DB_PASS') ?: ($dbPass ?? null);

// require_once dirname($_SERVER["DOCUMENT_ROOT"]) . '/etra.group/common/aws-sdk/aws-autoloader.php';
// loadEnv('/home/etra/.env');

$dbMasked = 0; // 1 or 0

if ($dbMasked == 1 && isset($devstage) && $devstage == 'test') {
    $dbName = 'etra_superviral_masked'; // masked
}

function sendCloudwatchData($namespace, $metricName, $func, $dimensions, $data)
{
    global $cloudwatchkey, $cloudwatchpassword;

    $cloudWatchClient = new CloudWatchClient([
        'region' => 'us-east-2',
        'version' => 'latest',
        'credentials' => [
            'key'    => $cloudwatchkey,
            'secret' => $cloudwatchpassword,
        ],
    ]);

    try {
        $cloudWatchClient->putMetricData([
            'Namespace' => $namespace,
            'MetricData' => [
                [
                    'MetricName' => $metricName,
                    'Dimensions' => [
                        [
                            'Name'  => $func,
                            'Value' => $dimensions
                        ],
                    ],
                    'Unit'  => 'None',
                    'Value' => $data,
                ],
            ],
        ]);

        error_log($metricName . ' metric data sent successfully');
    } catch (Exception $e) {
        error_log('Error sending metric data: ' . $e->getMessage());
    }
}

$conn = '';

// if($_GET['loadnew']=='true'){loadEnv('/home/etra/.env');}

if ($_SERVER['SERVER_NAME'] == "superviral.lcl" || $_SERVER['SERVER_NAME'] == "tikoid.lcl") {
    $protocol = 'http://';
} else {
    $protocol = 'https://';
}

$siteDomain = $protocol . $_SERVER['SERVER_NAME'];

// -------------------------
// Setup database connection (EKS / RDS aware)
// -------------------------

// Basic validation of DB config before connecting
// if (empty($dbHost) || empty($dbUser) || $dbPass === null) {
//     error_log('[superviral.io] Missing DB credentials - check DB_HOST/DB_USER/DB_PASS or Param Store.');
//     die('Temporary database configuration error.');
// }

// // Use RDS host instead of localhost
// $conn = mysql_connect($dbHost, $dbUser, $dbPass);

// if (!$conn) {
//     error_log('[superviral.io] DB connection failed: ' . mysqli_connect_error());
//     die('Temporary database connection error.');
// }

// if (!mysql_select_db($dbName, $conn)) {
//     error_log('[superviral.io] Selecting DB failed: ' . mysqli_error($conn));
//     die('Temporary database selection error.');
// }


// Setup database connection
$conn = mysql_connect ($dbHost , $dbUser , $dbPass) or die(mysql_error());

mysql_select_db ($dbName , $conn);


date_default_timezone_set('Europe/London');

/**
 * Legacy mysql_* compatibility wrappers using mysqli_*
 */
function mysql_connect($server, $username, $password)
{
    return mysqli_connect($server, $username, $password);
}

function mysql_select_db($database_name, $link)
{
    return mysqli_select_db($link, $database_name);
}

function mysql_query($query)
{
    global $conn;
    return mysqli_query($conn, $query);
}

function mysql_fetch_array($result)
{
    return mysqli_fetch_assoc($result);
}

function mysql_num_rows($result)
{
    return mysqli_num_rows($result);
}

function mysql_insert_id()
{
    global $conn;
    return mysqli_insert_id($conn);
}

/**
 * Legacy nameserver protection block
 * This used to enforce SERVER_NAME containing 'superviral.io'.
 * On EKS behind a LoadBalancer, SERVER_NAME will be the ELB hostname,
 * so this check breaks the app. We disable it for containerized env.
 */
/*
$webhookbypass = $webhookbypass ?? 0;
if ($webhookbypass == 0) {
    if (strpos($_SERVER['SERVER_NAME'], 'superviral.io') !== false) {
        // ok
    } else {
        die('Error'); // Protection from Nameserver imitation
    }
}
*/

//////////////////////////// CHANGED FROM HERE $LOC: how it's retrieveed

// MASTER 301 REDIRECT

$subdomaindetect = $_SERVER['SERVER_NAME'];
$subdomaindetect = str_replace('superviral.', '', $subdomaindetect);

$subdomaindetecthttp_host = $_SERVER['HTTP_HOST'];
$subdomainhttp_host_array = explode('.', $subdomaindetecthttp_host);

$subdomainloc = array_shift(($subdomainhttp_host_array));

if (($subdomainloc == 'us') || ($subdomainloc == 'uk')) {

    $_SERVER['REQUEST_URI'] = preg_replace(
        '/' . preg_quote('%7B') . '[\s\S]+?' . preg_quote('%7D') . '/',
        '',
        $_SERVER['REQUEST_URI']
    );
    $_SERVER['REQUEST_URI'] = str_replace('%7B%7D', '', $_SERVER['REQUEST_URI']);
    $cleanurl = str_replace(
        'superviral.io//',
        'superviral.io/',
        'https://superviral.io' . $_SERVER['REQUEST_URI']
    );

    $newhttphost = str_replace($subdomainloc . '.', '', $_SERVER['HTTP_HOST']);

    header('Location: https://' . $newhttphost . '/' . $subdomainloc . $_SERVER['REQUEST_URI'], TRUE, 301);
    die;
}

if (
    ($subdomainloc == 'www') ||
    ($subdomainloc == 'fr') ||
    ($subdomainloc == 'es') ||
    ($subdomainloc == 'it') ||
    ($subdomainloc == 'fr') ||
    ($subdomainloc == 'de')
) {

    $_SERVER['REQUEST_URI'] = preg_replace(
        '/' . preg_quote('%7B') . '[\s\S]+?' . preg_quote('%7D') . '/',
        '',
        $_SERVER['REQUEST_URI']
    );
    $_SERVER['REQUEST_URI'] = str_replace('%7B%7D', '', $_SERVER['REQUEST_URI']);
    $cleanurl = str_replace(
        'superviral.io//',
        'superviral.io/',
        'https://superviral.io' . $_SERVER['REQUEST_URI']
    );

    if ((strpos($_SERVER['REQUEST_URI'], '/uk/') !== false) || (strpos($_SERVER['REQUEST_URI'], '/us/') !== false)) {
        header('Location: ' . $cleanurl, TRUE, 301);
        die;
    } else {
        header('Location: ' . $cleanurl, TRUE, 301);
        die;
    }
}

// WE ARE RETRIEVING IT THROUGH GET

$loc = addslashes($_GET['loc']); //MAKE SURE THE GET STAYS HERE

if (empty($loc)) $loc = 'us';

if ($loc == 'superviral') $loc = 'us';

if ($loc == 'www') $loc = 'us';

if ($loc !== 'us') {
    $loclink        = '/' . $loc;
    $loclinkforward = $loc . '/';
}

$locas = array(
    "ww" => array(
        "sdb" => "ww",
        "countrycode" => "US",
        "currencysign" => "&dollar;",
        "currencyend" => "",
        "currencypp" => "USD",
        "mid" => "2573",
        "contentlanguage" => "en",
        "footercopyright" => "© 2012 - " . date("Y") . " Superviral. All Rights Reserved.",
        "order" => "order",
        "order1" => "details",
        "order1select" => "select",
        "order2" => "review",
        "order3" => "payment",
        "order3-new" => "payment-new",
        "order3-processing" => "payment-processing",
        "order4" => "finish",
        "account" => "account",
        "login" => "login",
        "logout" => "logout",
        "signup" => "sign-up",
        "forgotpassword" => "forgot-password",
        "resetpassword" => "reset-password"
    ),
    "uk" => array(
        "sdb" => "uk",
        "altsdb" => "us",
        "altlang" => "American English (US)",
        "countrycode" => "GB",
        "currencysign" => "&pound;",
        "currencyend" => "",
        "currencypp" => "GBP",
        "mid" => "2567",
        "contentlanguage" => "en-gb",
        "footercopyright" => "© 2012 - " . date("Y") . " Etra Ventures LTD trading as Superviral. All Rights Reserved.",
        "order" => "order",
        "order1" => "details",
        "order1select" => "select",
        "order2" => "review",
        "order3" => "payment",
        "order3-new" => "payment-new",
        "order3-processing" => "payment-processing",
        "order4" => "finish",
        "account" => "account",
        "login" => "login",
        "logout" => "logout",
        "signup" => "sign-up",
        "forgotpassword" => "forgot-password",
        "resetpassword" => "reset-password"
    ),
    "us" => array(
        "sdb" => "us",
        "altsdb" => "uk",
        "altlang" => "British English (GB)",
        "countrycode" => "US",
        "currencysign" => "$",
        "currencyend" => "",
        "currencypp" => "USD",
        "mid" => "2573",
        "contentlanguage" => "en-us",
        "footercopyright" => "© 2012 - " . date("Y") . " Superviral. All Rights Reserved.",
        "order" => "order",
        "order1" => "details",
        "order1select" => "select",
        "order2" => "review",
        "order3" => "payment",
        "order3-new" => "payment-new",
        "order3-processing" => "payment-processing",
        "order4" => "finish",
        "account" => "account",
        "login" => "login",
        "logout" => "logout",
        "signup" => "sign-up",
        "forgotpassword" => "forgot-password",
        "resetpassword" => "reset-password"
    ),
    "de" => array(
        "sdb" => "de",
        "countrycode" => "DE",
        "currencysign" => "&euro;",
        "currencyend" => "",
        "currencypp" => "EUR",
        "mid" => "2571",
        "contentlanguage" => "de",
        "footercopyright" => "© 2012 - " . date("Y") . " Etra Ventures Ltd, handelnd als Superviral. Alle Rechte vorbehalten.",
        "order" => "auftrag",
        "order1" => "einzelheiten",
        "order1select" => "wahlen",
        "order2" => "rezension",
        "order3" => "zahlung",
        "order3-processing" => "zahlungsabwicklung",
        "order4" => "fertig",
        "account" => "konto",
        "login" => "einloggen",
        "logout" => "ausloggen",
        "signup" => "anmelden",
        "forgotpassword" => "passwort-vergessen",
        "resetpassword" => "reset-password"
    ),
    "it" => array(
        "sdb" => "it",
        "countrycode" => "IT",
        "currencysign" => "&euro;",
        "currencyend" => "",
        "currencypp" => "EUR",
        "mid" => "2571",
        "contentlanguage" => "it",
        "footercopyright" => "© 2012 - " . date("Y") . " Etra Ventures Ltd opera in qualità di Superviral. Tutti i Diritti Riservati.",
        "order" => "ordine",
        "order1" => "dettagli",
        "order1select" => "selezionare",
        "order2" => "revisione",
        "order3" => "pagamento",
        "order3-processing" => "processo-di-pagamento",
        "order4" => "finire",
        "account" => "account",
        "login" => "accesso",
        "logout" => "disconnettersi",
        "signup" => "iscrizione",
        "forgotpassword" => "ha-dimenticato-la-password",
        "resetpassword" => "resetta-password"
    ),
    "es" => array(
        "sdb" => "es",
        "countrycode" => "ES",
        "currencysign" => "&euro;",
        "currencyend" => "",
        "currencypp" => "EUR",
        "mid" => "2571",
        "contentlanguage" => "es",
        "footercopyright" => "© 2012 - " . date("Y") . " Etra Ventures Ltd. Comercializando como Superviral. Todos los derechos reservados.",
        "order" => "orden",
        "order1" => "detalles",
        "order1select" => "seleccione",
        "order2" => "revision",
        "order3" => "pago",
        "order3-processing" => "procesando-el-pago",
        "order4" => "finalizar",
        "account" => "cuenta",
        "login" => "iniciar-sesion",
        "logout" => "cerrar-sesion",
        "signup" => "registrarse",
        "forgotpassword" => "olvidado-tu-contrasena",
        "resetpassword" => "restablecer-contrasena"
    ),
    "fr" => array(
        "sdb" => "fr",
        "countrycode" => "FR",
        "currencysign" => "",
        "currencyend" => " &euro;",
        "currencypp" => "EUR",
        "mid" => "2571",
        "contentlanguage" => "fr",
        "footercopyright" => "© 2012 - " . date("Y") . " Etra Ventures Ltd, sous le nom de Superviral. Tous droits réservés.",
        "order" => "ordre",
        "order1" => "detalles",
        "order1select" => "selectionner",
        "order2" => "revue",
        "order3" => "paiement",
        "order3-processing" => "traitement-paiements",
        "order4" => "terminer",
        "account" => "compte",
        "login" => "connexion",
        "logout" => "se-deconnecter",
        "signup" => "s-inscrire",
        "forgotpassword" => "mot-passe-oublie",
        "resetpassword" => "reinitialiser-mot-passe"
    )
);

$currency = $locas[$loc]['currencysign'];

if (($loc !== 'ww') && ($loc !== 'us') && ($loc !== 'uk')) {
    $notenglish = true;
}

?>
