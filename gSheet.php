<?php

require_once __DIR__ . '/vendor/autoload.php';


define('APPLICATION_NAME', 'Google Sheets API PHP');
define('CREDENTIALS_PATH', __DIR__ . '/credentials/sheets.googleapis.com-php.json');
define('CLIENT_SECRET_PATH', __DIR__ . '/client_secret.json');
// If modifying these scopes, delete your previously saved credentials
// at ~/.credentials/sheets.googleapis.com-php-quickstart.json
define('SCOPES', implode(' ', array(
    Google_Service_Sheets::SPREADSHEETS_READONLY)
));

/**
 * Returns an authorized API client.
 * @return Google_Client the authorized client object
 */
function getClient() {
    $client = new Google_Client();
    $client->setApplicationName(APPLICATION_NAME);
    $client->setScopes(SCOPES);
    $client->setAuthConfig(CLIENT_SECRET_PATH);
    $client->setAccessType('offline');

    // Load previously authorized credentials from a file.
    $credentialsPath = expandHomeDirectory(CREDENTIALS_PATH);
    if (file_exists($credentialsPath)) {
        $accessToken = json_decode(file_get_contents($credentialsPath), true);
        $client->setAccessToken($accessToken);
        // Refresh the token if it's expired.
        if ($client->isAccessTokenExpired()) {
            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            file_put_contents($credentialsPath, json_encode($client->getAccessToken()));
        }
        return $client;
    } else {
        // Request authorization from the user.
        $authUrl = $client->createAuthUrl();
        throw new Exception($authUrl, 191919);
    }
}

/**
 * Returns an authorized API client.
 * @return Google_Client the authorized client object
 */
function getAccessToken($authCode) {
    $client = new Google_Client();
    $client->setApplicationName(APPLICATION_NAME);
    $client->setScopes(SCOPES);
    $client->setAuthConfig(CLIENT_SECRET_PATH);
    $client->setAccessType('offline');
    $client->setRedirectUri('postmessage');
    $credentialsPath = expandHomeDirectory(CREDENTIALS_PATH);

    // Exchange authorization code for an access token.
    $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);

    // Store the credentials to disk.
    if (!file_exists(dirname($credentialsPath))) {
        mkdir(dirname($credentialsPath), 0700, true);
    }
    file_put_contents($credentialsPath, json_encode($accessToken));
    //printf("Credentials saved to %s\n", $credentialsPath);
    $client->setAccessToken($accessToken);

    // Refresh the token if it's expired.
    if ($client->isAccessTokenExpired()) {
        $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
        file_put_contents($credentialsPath, json_encode($client->getAccessToken()));
    }
    return $client;
}

/**
 * Expands the home directory alias '~' to the full path.
 * @param string $path the path to expand.
 * @return string the expanded path.
 */
function expandHomeDirectory($path) {
    $homeDirectory = getenv('HOME');
    if (empty($homeDirectory)) {
        $homeDirectory = getenv('HOMEDRIVE') . getenv('HOMEPATH');
    }
    return str_replace('~', realpath($homeDirectory), $path);
}

if (isset($_REQUEST["code"])) {
    getAccessToken($_REQUEST["code"]);
}

try {
    // Get the API client and construct the service object.
    $client = getClient();
} catch (Exception $exc) {
    if ($exc->getCode() ==191919)
    {
        echo json_encode(array("authUrl"=>$exc->getMessage()));
        die();
    }
    else
    {
        echo json_encode(array("error"=>$exc->getTraceAsString()));
        die();
    }
}

echo json_encode($client->getAccessToken());