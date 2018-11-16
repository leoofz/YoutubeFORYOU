<?php

if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
  throw new \Exception('please run "composer require google/apiclient:~2.0" in "' . __DIR__ .'"');
}

require_once __DIR__ . '/vendor/autoload.php';
session_start();

?>

<!doctype html>
<html>
  <head>
    <title>Youtube FOR YOU</title>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <link rel="stylesheet" type="text/css" href="css/stylesheet.css">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
 
  </head>
  <body>

    <header>

      <a href="search.php"><img src="img/YoutubeForYou_Logo@2x.png" alt="Youtube FOR YOU Logo" class="logo"></a>

      <div>

        <a href="my-uploads.php"><button class="viewUploads-btn">View Uploads</button></a>

      </div>

    </header>

    <section class="landing-section">

      <div class="landingText-container">
          <h1><span class="small-heading">Your own personalized</span> Youtube Playground</h1>

          <p>Avoid the distractions you find on Youtube’s website with a stripped down version, made just <strong>for you</strong></p>

        <?php

          // create form that uses the GET method to get data from Youtube API
          $searchForm = <<<END
          <form class="search-form" method="GET">
          <input type="search" id="q" name="q" placeholder="Search">
          <button id="search-btn" type="submit" value="Search">Search</button
          </form>
END;

        ?>

        <?=$searchForm?>

      </div>
    </section>

    <section class="searchResults-section">
      <h2>Search Results</h2>

      <?php

        $htmlBody = <<<END
END;

        // This code will execute if the user entered a search query in the form
        // and submitted the form.
        if (isset($_GET['q'])) {

          // Enter your developer key from your credentials page of the project
          // you set up in developer console. This one should be a regular API key.
          $DEVELOPER_KEY = 'REPLACE_ME';

          $client = new Google_Client();
          $client->setDeveloperKey($DEVELOPER_KEY);

          // Define an object that will be used to make all API requests.
          $youtube = new Google_Service_YouTube($client);

          $htmlBody = '';
          try {

          // Call the search.list method to retrieve results matching the specified
          // query term.
          $searchResponse = $youtube->search->listSearch('id,snippet', array(
            'q' => $_GET['q'],
            'maxResults' => 20,
          ));

          $videos = '';

      ?>

      <div class="videoResults-container">

        <?php

          foreach ($searchResponse['items'] as $searchResult) {

            // Echo each video player using the video ID and iframe
            echo '<iframe class="uploadedVideo-box"
            src="https://www.youtube.com/embed/' . $searchResult['id']['videoId'] . '"></iframe>';
            
          }

        ?>

      </div>

      <?php
        
          } catch (Google_Service_Exception $e) {
            $htmlBody .= sprintf('<p>A service error occurred: <code>%s</code></p>',
              htmlspecialchars($e->getMessage()));
          } catch (Google_Exception $e) {
            $htmlBody .= sprintf('<p>An client error occurred: <code>%s</code></p>',
              htmlspecialchars($e->getMessage()));
          }
        }
      ?>


      <div class="searchResultsPlaceholder-container">

        <img src="img/search-illustration.svg" alt="Search Illustration" class="search-illustration">

        <h3>Search Through YouTube's Entire Catalog!</h3>
        <p>Use the search bar above to search for some of your favourite videos and find them here!</p>

      </div>
      
    </section>
    
  </body>
  <script src="js/script.js"></script>
</html>