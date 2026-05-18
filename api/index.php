<?php
session_start();
$scores_file = __DIR__ . '/../scores.json';
if (!file_exists($scores_file)) {
    if (!getenv('VERCEL')) {
        @file_put_contents($scores_file, json_encode([]));
    }
}

$db_url = getenv("DATABASE_URL");
if (!$db_url) {
    // Fallback to the URL provided for local testing
    $db_url = "postgresql://neondb_owner:npg_RiaP2ez3sZGT@ep-billowing-wind-aqft0g7h-pooler.c-8.us-east-1.aws.neon.tech/neondb?sslmode=require";
}
$pdo = null;
$db_error = null;
if ($db_url) {
    $dbopts = parse_url($db_url);
    $port = isset($dbopts["port"]) ? $dbopts["port"] : 5432;
    $endpoint = explode('.', $dbopts["host"])[0];
    // For Neon SNI workaround on older clients, prepend the endpoint to the password
    $neon_pass = "endpoint={$endpoint};" . $dbopts["pass"];
    try {
        $pdo = new PDO(
            "pgsql:host={$dbopts["host"]};port={$port};dbname=".ltrim($dbopts["path"],'/').";sslmode=require",
            $dbopts["user"],
            $neon_pass
        );
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec("CREATE TABLE IF NOT EXISTS player_scores (
            name VARCHAR(255) PRIMARY KEY,
            score INT DEFAULT 0
        )");
    } catch (PDOException $e) {
        $db_error = "Database connection failed: " . $e->getMessage();
        $pdo = null;
    }
}

function get_all_scores($pdo, $scores_file) {
    if ($pdo) {
        $stmt = $pdo->query("SELECT name, score FROM player_scores ORDER BY score DESC");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $scores = [];
        foreach ($results as $row) {
            $scores[$row['name']] = $row['score'];
        }
        return $scores;
    } else {
        return json_decode(@file_get_contents($scores_file) ?: '{}', true) ?: [];
    }
}

function save_player_score($pdo, $scores_file, $name, $score) {
    if ($pdo) {
        $stmt = $pdo->prepare("INSERT INTO player_scores (name, score) VALUES (?, ?) ON CONFLICT (name) DO UPDATE SET score = EXCLUDED.score");
        $stmt->execute([$name, $score]);
    } else {
        $scores = get_all_scores($pdo, $scores_file);
        $scores[$name] = $score;
        if (!getenv('VERCEL')) {
            @file_put_contents($scores_file, json_encode($scores));
        }
    }
}

if (isset($_POST['set_name'])) {
    $name = trim($_POST['player_name']);
    if (!empty($name)) {
        $_SESSION['player_name'] = $name;
        
        $scores = get_all_scores($pdo, $scores_file);
        if (isset($scores[$name])) {
            $_SESSION['player_score'] = $scores[$name];
        } else {
            $_SESSION['player_score'] = 0;
            save_player_score($pdo, $scores_file, $name, 0);
        }
    }
}

if (isset($_POST['end_game'])) {
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

$choices = ['rock', 'paper', 'scissor'];
$result = '';
$sa_asul = '';
$sa_pula = '';
$result_style = '';
$outcome = '';
$final_name = '';
$final_score = 0;

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
        save_player_score($pdo, $scores_file, $_SESSION['player_name'], $_SESSION['player_score']);
    } elseif ($result_style === 'lose') {
        $final_name = $_SESSION['player_name'];
        $final_score = $_SESSION['player_score'];
        session_unset();
        session_destroy();
    }
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
    <?php if ($db_error): ?>
        <div style="background: red; color: white; padding: 10px; margin-bottom: 20px;">
            <strong>DATABASE ERROR:</strong> <?= htmlspecialchars($db_error) ?><br>
            <em>(If you see "could not find driver" locally, you need to enable extension=pdo_pgsql in your php.ini file!)</em>
        </div>
    <?php endif; ?>

    <div class="leaderboard">
        <h3>Leaderboard</h3>
        <?php
        $scores = get_all_scores($pdo, $scores_file);
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
    <?php endif; ?>

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
            <?php if ($result_style === 'lose' && $final_name): ?>
                <div style="margin-top: 15px; border-top: 2px dashed white; padding-top: 15px; width: 100%;">
                    <h3 style="color: yellow; margin: 0 0 10px 0;">Game Over, <?= htmlspecialchars($final_name) ?>!</h3>
                    <p style="margin: 0; font-size: 18px;">Final Score: <strong><?= $final_score ?></strong></p>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if (!isset($_SESSION['player_name'])): ?>
            <button class="reset-btn" onclick="window.location.href=window.location.href" style="font-size: 16px;">Play Again</button>
        <?php else: ?>
            <button class="reset-btn" onclick="window.location.href=window.location.href">Next</button>
        <?php endif; ?>
    <?php endif; ?>
</body>
</html>
