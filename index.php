<?php
session_start();
 
// Simulated user and tweet data (replace with a database in production)
$users = isset($_SESSION['users']) ? $_SESSION['users'] : [
    'user1' => ['name' => 'John Doe', 'followers' => [], 'following' => []],
    'user2' => ['name' => 'Jane Smith', 'followers' => [], 'following' => []]
];
$tweets = isset($_SESSION['tweets']) ? $_SESSION['tweets'] : [
    ['id' => 1, 'user' => 'user1', 'content' => 'Hello, world!', 'timestamp' => time()],
    ['id' => 2, 'user' => 'user2', 'content' => 'Loving this app!', 'timestamp' => time()]
];
$current_user = isset($_SESSION['current_user']) ? $_SESSION['current_user'] : 'user1';
 
// Handle tweet creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tweet_content'])) {
    $tweets[] = [
        'id' => count($tweets) + 1,
        'user' => $current_user,
        'content' => htmlspecialchars($_POST['tweet_content']),
        'timestamp' => time()
    ];
    $_SESSION['tweets'] = $tweets;
    header('Location: index.php');
    exit;
}
 
// Handle tweet deletion
if (isset($_GET['delete'])) {
    $tweet_id = (int)$_GET['delete'];
    $tweets = array_filter($tweets, fn($tweet) => $tweet['id'] !== $tweet_id || $tweet['user'] !== $current_user);
    $_SESSION['tweets'] = array_values($tweets);
    header('Location: index.php');
    exit;
}
 
// Handle tweet editing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_tweet_id'])) {
    $tweet_id = (int)$_POST['edit_tweet_id'];
    foreach ($tweets as &$tweet) {
        if ($tweet['id'] === $tweet_id && $tweet['user'] === $current_user) {
            $tweet['content'] = htmlspecialchars($_POST['edit_tweet_content']);
            break;
        }
    }
    $_SESSION['tweets'] = $tweets;
    header('Location: index.php');
    exit;
}
 
// Handle follow/unfollow
if (isset($_GET['follow'])) {
    $target_user = $_GET['follow'];
    if (!in_array($target_user, $users[$current_user]['following'])) {
        $users[$current_user]['following'][] = $target_user;
        $users[$target_user]['followers'][] = $current_user;
    }
    $_SESSION['users'] = $users;
    header('Location: index.php');
    exit;
}
if (isset($_GET['unfollow'])) {
    $target_user = $_GET['unfollow'];
    $users[$current_user]['following'] = array_diff($users[$current_user]['following'], [$target_user]);
    $users[$target_user]['followers'] = array_diff($users[$target_user]['followers'], [$current_user]);
    $_SESSION['users'] = $users;
    header('Location: index.php');
    exit;
}
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Twitter</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        body {
            background: #f0f2f5;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            padding: 20px;
            background: #1da1f2;
            color: white;
            border-radius: 10px 10px 0 0;
        }
        .tweet-form {
            background: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .tweet-form textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            resize: none;
        }
        .tweet-form button {
            background: #1da1f2;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 20px;
            cursor: pointer;
            margin-top: 10px;
        }
        .tweet-form button:hover {
            background: #0d95e8;
        }
        .tweet {
            background: white;
            padding: 15px;
            margin: 10px 0;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .tweet-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .tweet-header span {
            font-weight: bold;
        }
        .tweet-actions a {
            margin-left: 10px;
            text-decoration: none;
            color: #1da1f2;
        }
        .tweet-actions a:hover {
            text-decoration: underline;
        }
        .follow-section {
            margin: 20px 0;
            padding: 15px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .follow-section a {
            color: #1da1f2;
            text-decoration: none;
            margin-right: 10px;
        }
        .follow-section a:hover {
            text-decoration: underline;
        }
        @media (max-width: 600px) {
            .container {
                padding: 10px;
            }
            .tweet-form textarea {
                font-size: 14px;
            }
            .tweet-form button {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Simple Twitter</h1>
            <p>Welcome, <?php echo htmlspecialchars($users[$current_user]['name']); ?>!</p>
        </div>
 
        <!-- Tweet Form -->
        <div class="tweet-form">
            <form method="POST">
                <textarea name="tweet_content" rows="4" placeholder="What's happening?" required></textarea>
                <button type="submit">Tweet</button>
            </form>
        </div>
 
        <!-- Follow System -->
        <div class="follow-section">
            <h3>Users</h3>
            <?php foreach ($users as $username => $user): ?>
                <?php if ($username !== $current_user): ?>
                    <div>
                        <span><?php echo htmlspecialchars($user['name']); ?></span>
                        <?php if (in_array($username, $users[$current_user]['following'])): ?>
                            <a href="?unfollow=<?php echo $username; ?>">Unfollow</a>
                        <?php else: ?>
                            <a href="?follow=<?php echo $username; ?>">Follow</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
            <p>Following: <?php echo count($users[$current_user]['following']); ?> | Followers: <?php echo count($users[$current_user]['followers']); ?></p>
        </div>
 
        <!-- Tweet Feed -->
        <div class="tweet-feed">
            <h3>Tweet Feed</h3>
            <?php foreach ($tweets as $tweet): ?>
                <div class="tweet">
                    <div class="tweet-header">
                        <span><?php echo htmlspecialchars($users[$tweet['user']]['name']); ?></span>
                        <span><?php echo date('M d, Y H:i', $tweet['timestamp']); ?></span>
                    </div>
                    <p><?php echo htmlspecialchars($tweet['content']); ?></p>
                    <?php if ($tweet['user'] === $current_user): ?>
                        <div class="tweet-actions">
                            <a href="?delete=<?php echo $tweet['id']; ?>">Delete</a>
                            <a href="?edit=<?php echo $tweet['id']; ?>">Edit</a>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($_GET['edit']) && (int)$_GET['edit'] === $tweet['id'] && $tweet['user'] === $current_user): ?>
                        <form method="POST">
                            <textarea name="edit_tweet_content" rows="4" required><?php echo htmlspecialchars($tweet['content']); ?></textarea>
                            <input type="hidden" name="edit_tweet_id" value="<?php echo $tweet['id']; ?>">
                            <button type="submit">Update Tweet</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
