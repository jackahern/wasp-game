<?php
    include_once('db.php');
    // Truncate all tables -- example truncate below:
    $conn->prepare("TRUNCATE TABLE wasps")->execute();
    $conn->prepare("TRUNCATE TABLE game")->execute();
    $conn->prepare("TRUNCATE TABLE killed_wasps")->execute();

    // Print statement to express the tables have been truncated
    echo "Truncated all tables for the wasp game<br>";

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
    $seed = $_GET['seed'] ?? NULL;
    if ($seed == 'seed') {
        foreach ($randWasps as $waspStats) {
            $sql = "INSERT INTO wasps (wasp_type, wasp_points)
            VALUES (:wasp_type, :wasp_points)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':wasp_type' => $waspStats['type'],
                ':wasp_points' => $waspStats['points']
            ]);
        }
        echo "Produced " . count($randWasps) . " wasps into the hive!<br>";
    }
