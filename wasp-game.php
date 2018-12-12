<?php
include_once('db.php');
include_once('functions.php');
define('GAME_TOTAL_WASPS', 14);
define('WASPS', [
    "Queen" => [
        "amount" => 1,
        "points" => 80
    ],
    "Worker" => [
        "amount" => 5,
        "points" => 68
    ],
    "Drone" => [
        "amount" => 8,
        "points" => 60
    ]
]);
session_start();
$action = null;
$msg = null;
$game = getGameData();
$randWasps = getWaspData($game['game_id']);
$killedWaspData = getKilledWaspData($game['game_id']);
if (isset($_POST['action'])) {
    // If an action is set, the post value of that action is assigned to a variable called action
    $action = $_POST['action'];
}
if (!isset($game['game_status'])) {
    // If the game status is not set, the game has not been started, that is then reflected in the game status variable
    $game['game_status'] = 'not started';
}
if ($game['game_status'] == 'finished') {
    $msg = 'The game has been finished, please start new game if you wish to play again';
}
if ($action == 'start_game') {
    // Start game setup
    $msg = 'New game started, ' . count($randWasps) . ' wasps inserted into the hive';
    cancelExistingGame();
    startNewGame();
    populateWaspNest();
    // Automatically reload the pages elements
    redirect($msg);
    // End of game setup
}
else if ($action == 'hitting') {
    // Start of game functionality
    $game['game_status'] = 'started';
    // Pick a wasp out of the randWasps array at random, this will be the was classed as 'hit'
    $waspRandId = array_rand($randWasps, 1);
    $waspHit = $randWasps[$waspRandId];
    $msg = $waspHit['wasp_type'] . ' hit!';
    // Now comes several 'if' statements to decipher how many points the user gains and how many points the wasp loses
    if ($waspHit['wasp_type'] == 'Queen') {
        // Take away 7 hit points from the queen
        $waspHit['wasp_points'] -=7;
        $game['game_score'] +=7;
    }
    else if ($waspHit['wasp_type'] == 'Worker') {
        //Take away 10 points from the worker
        $waspHit['wasp_points'] -=10;
        $game['game_score'] +=10;
    }
    else if ($waspHit['wasp_type'] == 'Drone') {
        //Take away 12 points from the worker
        $waspHit['wasp_points'] -=12;
        $game['game_score'] +=7;
    }
    if ($waspHit['wasp_points'] <= 0) {
        // When a wasp is hit and they have 0 points they should now be added to the killed wasps table
        killWasp($waspHit['wasp_id'], $game['game_id']); 
        $game['game_total_wasps']--;
        $msg = 'A ' . $waspHit['wasp_type'] . ' is now dead!';
    }
    if ($game['game_total_wasps'] === 0 ) {
        $game['game_status'] = 'finished';
        $msg = 'There are no wasps left in the hive, you\'ve only gone and killed them all!';
    }
    // Update the database tables to have up to date gameplay values
    updateWaspDetails($waspHit['wasp_points'], $waspHit['wasp_id']);
    updateGameDetails($game);
	header("Location: wasp-game.php");
    die();
if (isset($_SESSION['msg'])) {
    $msg = $_SESSION['msg'];
    unset($_SESSION['msg']);
}
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Wasp Killing Game</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" media="screen" href="wasp-styles.css" />
</head>
<body>
    <h1>
    <?= $msg; ?>
    </h1>
    <main>
    <h1>Wasp nest!</h1>
    <ul>
    <?php
    foreach ($randWasps as $wasp) {
        ?>
        <li>
        <?= $wasp['wasp_type'] . ':' . $wasp['wasp_points']; ?>
        <br>
        </li>
        <?php
    }
    ?>
    </ul>
    <h2>Game Stats</h2>
    <p>
        <strong>Wasps remaining: </strong>
        <?= $game['game_total_wasps']; ?>
    </p>
    <p>
        <strong>Game score: </strong>
        <?= $game['game_score']; ?>
    </p>
    <form method="POST" action="<?= $_SERVER['PHP_SELF']; ?>">
        <?php
        if ($game['game_status'] == 'started') {
            ?>
                <button type="submit" name="action" value="hitting">Hit Wasp!</button>
            <?php
        }
        else {
            ?>
                <button type="submit" name="action" value="start_game">Start New Game</button>
            <?php
        }
            ?>
    </form>
    </main>
</body>
</html>
