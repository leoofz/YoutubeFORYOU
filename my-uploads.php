<?php

if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
  throw new \Exception('please run "composer require google/apiclient:~2.0" in "' . __DIR__ .'"');
}

require_once __DIR__ . '/vendor/autoload.php';
session_start();


// You can find the Client ID in your projects credentials
// after you have set created an OAuth client id key

// You can find the Client Secret once the Client ID is set up
// by downloading the json file from the download button next to your client id
// in the developer console page. The secret key will be in the json file.

// Enter OAuth2 Client ID + Client Secret Keys here
$OAUTH2_CLIENT_ID = 'REPLACE_ME';
$OAUTH2_CLIENT_SECRET = 'REPLACE_ME';

$client = new Google_Client();
$client->setClientId($OAUTH2_CLIENT_ID);
$client->setClientSecret($OAUTH2_CLIENT_SECRET);
$client->setScopes('https://www.googleapis.com/auth/youtube');
$redirect = filter_var('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'],
  FILTER_SANITIZE_URL);
$client->setRedirectUri($redirect);

// Define an object that will be used to make all API requests.
$youtube = new Google_Service_YouTube($client);

// Check if an auth token exists for the required scopes
$tokenSessionKey = 'token-' . $client->prepareScopes();
if (isset($_GET['code'])) {
  if (strval($_SESSION['state']) !== strval($_GET['state'])) {
    die('The session state did not match.');
  }

  $client->authenticate($_GET['code']);
  $_SESSION[$tokenSessionKey] = $client->getAccessToken();
  header('Location: ' . $redirect);
}

if (isset($_SESSION[$tokenSessionKey])) {
  $client->setAccessToken($_SESSION[$tokenSessionKey]);
}

?>

<!doctype html>
<html>
  <head>
    <title>Youtube FOR YOU</title>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <link rel="stylesheet" type="text/css" href="css/stylesheet.css">
    <script src="js/script.js"></script>
  </head>
  <body>

    <header>

      <a href="search.php"><img src="img/YoutubeForYou_Logo@2x.png" alt="Youtube FOR YOU Logo" class="logo"></a>

      <div>

        <button class="viewUploads-btn">View Uploads</button>

      </div>

    </header>

    <section class="uploads-landing-section">

      <h1>Your Uploads</h1>

    </section>

  <section class="userUploads-section">
    
    <h2>Most Recent Uploads</h2>

    <?php

    // Check to ensure that the access token was successfully acquired.
    if ($client->getAccessToken()) {
    try {

    // Call the channels.list method to retrieve information about the
    // currently authenticated user's channel.
    $channelsResponse = $youtube->channels->listChannels('contentDetails', array(
    'mine' => 'true',
    ));

    $htmlBody = '';
    foreach ($channelsResponse['items'] as $channel) {

    // Extract the unique playlist ID that identifies the list of videos
    // uploaded to the channel, and then call the playlistItems.list method
    // to retrieve that list.
    $uploadsListId = $channel['contentDetails']['relatedPlaylists']['uploads'];

    $playlistItemsResponse = $youtube->playlistItems->listPlaylistItems('snippet', array(
    'playlistId' => $uploadsListId,
    'maxResults' => 50
    ));

    ?>

  <!-- Uploaded videos load into this container -->
  <div class="uploadedVideoResults-container">

    <?php
    foreach ($playlistItemsResponse['items'] as $playlistItem) {

    // Output the uploaded videos from the account
    echo '<iframe class="uploadedVideo-box"
    src="https://www.youtube.com/embed/'. $playlistItem['snippet']['resourceId']['videoId'] .'"></iframe>';
    ?>

    <?php
    
    }

    }
    } catch (Google_Service_Exception $e) {
    $htmlBody = sprintf('<p>A service error occurred: <code>%s</code></p>',
    htmlspecialchars($e->getMessage()));
    } catch (Google_Exception $e) {
    $htmlBody = sprintf('<p>An client error occurred: <code>%s</code></p>',
    htmlspecialchars($e->getMessage()));
    }

    ?>

  </div>

  <?php

  $_SESSION[$tokenSessionKey] = $client->getAccessToken();
  } elseif ($OAUTH2_CLIENT_ID == 'REPLACE_ME') {

  // Makes sure the user sets up their id before trying to use the files
  $htmlBody = <<<END
  <h3>Client Credentials Required</h3>
  <p>
  You need to set <code>\$OAUTH2_CLIENT_ID</code> and
  <code>\$OAUTH2_CLIENT_ID</code> before proceeding.
  <p>
END;
  } else {
  $state = mt_rand();
  $client->setState($state);
  $_SESSION['state'] = $state;

  // Display illustration and link to authorize if no access token exists
  $authUrl = $client->createAuthUrl();
  $htmlBody = <<<END
  <div class="signIn-section">
        <img src="img/empty-illustration.svg" alt="Missing Uploads Illustration" class="empty-illustration">
        <h3>Sign In to View Your Uploads!</h3>
        <p>View your own videos to show your friends and family by signing in to your Google account!</p>
        <a href="$authUrl"><button class="signIn-btn">Sign In with Google</button></a>
  </div>

END;
  }

?>

    <?=$htmlBody?>
  
  </body>
</html>

