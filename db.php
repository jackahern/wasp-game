<?php
$servername = "localhost";
$username = "root";
$password = "admin";
$dbname = "wasp-game";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
catch(PDOException $e) {
    echo "connection failed: " . $e->getMessage();
    }
// DB functions for reference in the main wasp-game.php file
function getGameData () {
	global $conn;
	$gameSql = "SELECT * 
		FROM game
		ORDER BY game_id desc 
		LIMIT 1";
	$gameStmt = $conn->prepare($gameSql);
	$gameStmt->execute();
	$game = $gameStmt->fetch(PDO::FETCH_ASSOC);
	return $game;
}
function getWaspData($game_id) {
    global $conn;
    $waspSql = "SELECT w.* FROM wasps AS w
    LEFT JOIN killed_wasps AS k ON k.wasp_id = w.wasp_id AND game_id = :game_id
    WHERE k.killed_wasp_id is NULL";
    $waspStmt = $conn->prepare($waspSql);
    $waspStmt->execute([
        ':game_id' => $game_id
    ]);
    $randWasps = $waspStmt->fetchAll(PDO::FETCH_ASSOC);
    return $randWasps;
}
function populateWaspNest() {
    global $conn;
    $conn->prepare("TRUNCATE TABLE wasps")->execute();
    $wasps = [
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
    ];
    $randWasps = [];
    foreach ($wasps as $wasp => $waspStats) {
        for ($i=0; $i < $waspStats['amount']; $i++) {
            $randWasps[] = [
                "type" => $wasp,
                "points" => $waspStats['points']
            ];
        }
    }
    shuffle($randWasps);
    foreach ($randWasps as $waspStats) {
        $sql = "INSERT INTO wasps (wasp_type, wasp_points)
        VALUES (:wasp_type, :wasp_points)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':wasp_type' => $waspStats['type'],
            ':wasp_points' => $waspStats['points']
        ]);
    }  
}
function getKilledWaspData($game_id) {
    global $conn;
    $killedWaspSql = "SELECT k.killed_wasp_id, k.wasp_id, w.wasp_type, w.wasp_points
    FROM killed_wasps AS k
    JOIN wasps AS w
    ON k.wasp_id = w.wasp_id
    WHERE game_id = :game_id";
    $killedWaspStmt = $conn->prepare($killedWaspSql);
    $killedWaspStmt->execute([
        ':game_id' => $game_id
    ]);
}
function cancelExistingGame() {
    global $conn;
    $cancelExistingSql = "UPDATE game
    SET game_status = 'cancelled'
    WHERE game_status = 'started'";
    $cancelExistingStmt = $conn->prepare($cancelExistingSql);
    $cancelExistingStmt->execute();
}
function startNewGame() {
    global $conn;
    $startNewGameSql = "INSERT INTO game (game_status, game_score, game_total_wasps)
    VALUES ('started', 0, :GAME_TOTAL_WASPS)";
    $startNewGameStmt = $conn->prepare($startNewGameSql);
    $startNewGameStmt->execute([
        ':GAME_TOTAL_WASPS' => GAME_TOTAL_WASPS
    ]);
}
function killWasp($wasp_id, $game_id) {
    global $conn;
    $addKilledWaspSql = "INSERT INTO killed_wasps (wasp_id, game_id)
        VALUES (:wasp_id, :game_id)";
    $addKilledWaspStmt = $conn->prepare($addKilledWaspSql);
    $addKilledWaspStmt->execute([
        ':wasp_id' => $wasp_id,
        ':game_id' => $game_id
    ]);
}
function updateGameDetails($game) {
    global $conn;
    $updateGameSql = 'UPDATE game
        SET game_status = :game_status, game_score = :game_score, game_total_wasps = :game_total_wasps
        WHERE game_id = :game_id';
    $updateGameStmt = $conn->prepare($updateGameSql);
    $updateGameStmt->execute([
        ':game_id' => $game['game_id'],
        ':game_status' => $game['game_status'],
        ':game_score' => $game['game_score'],
        ':game_total_wasps' => $game['game_total_wasps']
    ]);
}
function updateWaspDetails($wasp_hit, $wasp_id) {
    global $conn;
    $updateWaspSql = 'UPDATE wasps
    SET wasp_points = :wasp_points
    WHERE wasp_id = :wasp_id';
    $updateWaspStmt = $conn->prepare($updateWaspSql);
    $updateWaspStmt->execute([
        ':wasp_points' => $wasp_hit,
        ':wasp_id' => $wasp_id
    ]);
}