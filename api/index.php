<?php
session_start();
$scores_file = __DIR__ . '/../scores.json';
if (!file_exists($scores_file)) {
    @file_put_contents($scores_file, json_encode([]));
}

if (isset($_POST['set_name'])) {
    $name = trim($_POST['player_name']);
    if (!empty($name)) {
        $_SESSION['player_name'] = $name;
        $_SESSION['player_score'] = 0;
        
        $scores = json_decode(file_get_contents($scores_file), true);
        if (isset($scores[$name])) {
            $_SESSION['player_score'] = $scores[$name];
        } else {
            $scores[$name] = 0;
            @file_put_contents($scores_file, json_encode($scores));
        }
    }
}

if (isset($_POST['end_game'])) {
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Rock Paper Scissors</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Silkscreen:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            background-image: url('https://i.pinimg.com/originals/60/ef/50/60ef50891cb918dc68f487e7abf0e22b.gif'); 
            background-repeat: no-repeat;
            background-position: center;
            background-size: cover;
            height: 700px;
            font-family: "Silkscreen", sans-serif;
            text-align: center;
            color: white;
            text-shadow: 1px 1px 2px black;
        }

        h1 {
            font-family: "Silkscreen", sans-serif;
            font-size: 50px;
            margin-bottom: 22.5px;
            padding-top: 20px;
        }

        form button {
            background: none;
            border: none;
            cursor: pointer;
            background-position: center;
            margin: 0 15px;
            transition: transform 0.2s ease;
        }

        form button:hover {
            transform: scale(1.1);
        }

        form img {
            width: 100px;
            height: auto;
        }

        .result {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 20px auto;
            width: 450px;
            padding: 30px;
            border-style: solid;
            background-color: rgba(0, 0, 0, 0.7);
            border-radius: 10px;
        }

        .result.win {
            background-color: rgba(0, 128, 0, 0.7);
        }

        .result.lose {
            background-color: rgba(158, 0, 0, 0.7);
        }

        .result.tie {
            background-color: rgba(128, 128, 128, 0.7);
        }

        .result img {
            width: 80px;
        }

        .choices {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin: 15px 0;
        }

        .reset-btn {
            padding: 15px 25px;
            margin-top: 30px;
            height: 95px;
            width: 95px; 
            font-size: 20px;
            text-align: justify;
            text-decoration: none;
            outline: none;
            color: #fff;
            background-color:rgb(219, 0, 0);
            border: none;
            border-radius: 100%;
            box-shadow: 0 10px rgb(190, 0, 0);
            font-family: "Silkscreen", sans-serif;
            cursor: pointer;
        }

        .reset-btn:hover {
            background-color: rgb(255, 0, 0);
        }

        .outcome {
            font-size: 20px;
            margin-top: 10px;
        }
        
        .leaderboard {
            position: absolute;
            top: 20px;
            left: 20px;
            background-color: rgba(0, 0, 0, 0.7);
            padding: 15px;
            border-radius: 10px;
            text-align: left;
            border: 2px solid white;
            min-width: 200px;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .leaderboard h3 {
            margin-top: 0;
            border-bottom: 1px solid white;
            padding-bottom: 5px;
            font-size: 20px;
        }
        
        .leaderboard ol {
            padding-left: 25px;
            margin-bottom: 0;
            font-size: 16px;
        }
        
        .name-form {
            background-color: rgba(0, 0, 0, 0.7);
            padding: 40px;
            border-radius: 10px;
            display: inline-block;
            margin-top: 50px;
            border: 2px solid white;
        }
        
        .name-form input {
            padding: 10px;
            font-size: 16px;
            font-family: "Silkscreen", sans-serif;
            margin-bottom: 20px;
            width: 80%;
            text-align: center;
        }
        
        .name-form button {
            padding: 10px 20px;
            font-size: 16px;
            font-family: "Silkscreen", sans-serif;
            background-color: #28a745;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }
        
        .name-form button:hover {
            background-color: #218838;
        }
        
        .end-btn {
            padding: 10px 20px;
            margin-top: 20px;
            font-size: 16px;
            font-family: "Silkscreen", sans-serif;
            background-color: #dc3545;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }
        
        .end-btn:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <div class="leaderboard">
        <h3>Leaderboard</h3>
        <?php
        $scores = json_decode(file_get_contents($scores_file), true);
        if (empty($scores)) {
            echo "<p>No scores yet.</p>";
        } else {
            arsort($scores);
            echo "<ol>";
            foreach ($scores as $p_name => $score) {
                echo "<li>" . htmlspecialchars($p_name) . " - " . $score . "</li>";
            }
            echo "</ol>";
        }
        ?>
    </div>

    <h1>Rock-Paper-Scissors</h1>

    <?php if (!isset($_SESSION['player_name'])): ?>
        <div class="name-form">
            <h2>Enter your name to play</h2>
            <form method="POST">
                <input type="text" name="player_name" required placeholder="Your Name"><br>
                <button type="submit" name="set_name">Start Game</button>
            </form>
        </div>
    <?php else: ?>
        <h2>Welcome, <?= htmlspecialchars($_SESSION['player_name']) ?>! Score: <?= $_SESSION['player_score'] ?></h2>
        <form method="POST">
            <button type="submit" name="choice" value="rock">
                <img src="/gang_sign/rock.png" alt="Rock">
            </button>
            <button type="submit" name="choice" value="paper">
                <img src="/gang_sign/paper.png" alt="Paper">
            </button>
            <button type="submit" name="choice" value="scissor">
                <img src="/gang_sign/scissor.png" alt="Scissors">
            </button>
        </form>
        <form method="POST">
            <button type="submit" name="end_game" class="end-btn">End Game</button>
        </form>

    <?php
    $choices = ['rock', 'paper', 'scissor'];
    $result = '';
    $sa_asul = '';
    $sa_pula = '';
    $result_style = '';
    $outcome = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['choice'])) {
        $sa_asul = $_POST['choice'];
        $sa_pula = $choices[array_rand($choices)];

        $laban = "{$sa_asul}_{$sa_pula}";

        switch ($laban) {
            case 'rock_rock':
            case 'paper_paper':
            case 'scissor_scissor':
                $result = "It's a tie!";
                $outcome = "Both chose $sa_asul";
                $result_style = 'tie';
                break;

            case 'rock_scissor':
                $result = "You win!";
                $outcome = "Rock crushes scissors";
                $result_style = 'win';
                break;
                
            case 'scissor_rock':
                $result = "You lose!";
                $outcome = "Rock crushes scissors";
                $result_style = 'lose';
                break;

            case 'paper_rock':
                $result = "You win!";
                $outcome = "Paper covers rock";
                $result_style = 'win';
                break;
                
            case 'rock_paper':
                $result = "You lose!";
                $outcome = "Paper covers rock";
                $result_style = 'lose';
                break;

            case 'scissor_paper':
                $result = "You win!";
                $outcome = "Scissors cut paper";
                $result_style = 'win';
                break;
                
            case 'paper_scissor':
                $result = "You lose!";
                $outcome = "Scissors cut paper";
                $result_style = 'lose';
                break;

            default:
                $result = "Unexpected result.";
                $outcome = "Try again";
                $result_style = 'tie';
                break;
        }

        if ($result_style === 'win' && isset($_SESSION['player_name'])) {
            $_SESSION['player_score']++;
            $scores = json_decode(file_get_contents($scores_file), true);
            $scores[$_SESSION['player_name']] = $_SESSION['player_score'];
            @file_put_contents($scores_file, json_encode($scores));
        }
    }
    ?>

    <?php if ($result): ?>
        <div class="result <?php echo $result_style; ?>">
            <div class="choices">
                <div>
                    <p>Player</p>
                    <img src="/gang_sign/<?= $sa_asul ?>.png" alt="<?= $sa_asul ?>">
                </div>
                <div>
                    <p>Computer</p>
                    <img src="/gang_sign/<?= $sa_pula ?>.png" alt="<?= $sa_pula ?>">
                </div>
            </div>
            <h2><?= $result ?></h2>
            <p class="outcome"><?= $outcome ?></p>
        </div>
        
        <button class="reset-btn" onclick="window.location.href=window.location.href">Reset</button>
    <?php endif; ?>
    <?php endif; ?>
</body>
</html>
