<?php
declare(strict_types=1);

session_start();

if (empty($_SESSION['user'])) {
    require __DIR__ . '/sddm.php';
    exit;
}

$currentUser = htmlspecialchars($_SESSION['user'], ENT_QUOTES, 'UTF-8');

function asset(string $path): string {
    $fullPath = __DIR__ . '/' . ltrim($path, '/');
    $version = file_exists($fullPath) ? (string) filemtime($fullPath) : '1';
    return htmlspecialchars($path . '?v=' . $version, ENT_QUOTES, 'UTF-8');
}

$config = require __DIR__ . '/desktop_config.php';

$desktopIcons = $config['desktopIcons'];
$virtualFiles = $config['virtualFiles'];
$games        = $config['games'];
$apps         = $config['apps'] ?? [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>my cozy web corner</title>
    <link rel="icon" type="image/png" href="<?= asset('favicon.png') ?>">
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/memory.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/shifumi.css') ?>">

    <script defer src="<?= asset('js/script.js') ?>"></script>

    <?php foreach ($games as $game): ?>
        <?php if (!empty($game['script'])): ?>
            <script defer src="<?= asset($game['script']) ?>"></script>
        <?php endif; ?>
    <?php endforeach; ?>
</head>
<body>
    <main id="desktop">
        <!-- BASE & DYNAMIC DESKTOP ICONS -->
        <?php foreach ($desktopIcons as $icon): ?>
            <div class="desktop-icon" 
                 <?= !empty($icon['target']) ? 'data-target="' . htmlspecialchars($icon['target']) . '"' : '' ?> 
                 style="left: 0px; top: <?= (int)$icon['top'] ?>px;">
                <div class="icon-img">
                    <img src="<?= htmlspecialchars($icon['icon']) ?>" alt="<?= htmlspecialchars($icon['label']) ?>">
                </div>
                <span><?= htmlspecialchars($icon['label']) ?></span>
            </div>
        <?php endforeach; ?>

        <!-- GAMES WINDOW -->
        <section class="window" id="window-explorer" aria-labelledby="title-explorer" hidden>
            <div class="title-bar">
                <span id="title-explorer" class="title-bar-text">GAMES.EXE</span>
                <div class="title-bar-buttons">
                    <button type="button" class="title-bar-button btn-close" data-close="window-explorer" aria-label="Close Explorer">x</button>
                </div>
            </div>
            <div class="window-body">
                <div id="popup-project-list" class="grid">
                    <?php foreach ($games as $game): ?>
                        <button type="button" class="btn" data-target="<?= htmlspecialchars($game['target']) ?>">
                            <?= htmlspecialchars($game['label']) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- FILE MANAGER WINDOW -->
        <section class="window" id="window-filemanager" aria-labelledby="title-filemanager" hidden>
            <div class="title-bar">
                <span id="title-filemanager" class="title-bar-text">FILE_MANAGER.EXE</span>
                <div class="title-bar-buttons">
                    <button type="button" class="title-bar-button btn-close" data-close="window-filemanager" aria-label="Close File Manager">x</button>
                </div>
            </div>
            <div class="window-body">
                <div class="file-grid">
                    <?php foreach ($virtualFiles as $file): ?>
                        <div class="file-item">
                            <img src="<?= htmlspecialchars($file['icon']) ?>" alt="<?= htmlspecialchars($file['name']) ?>">
                            <span><?= htmlspecialchars($file['name']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- MEMORY GAME WINDOW -->
        <section class="window" id="window-memory" aria-labelledby="title-memory" hidden>
            <div class="title-bar">
                <span id="title-memory" class="title-bar-text">MEMORY.EXE</span>
                <div class="title-bar-buttons">
                    <button type="button" class="title-bar-button btn-close" data-close="window-memory" aria-label="Close Memory">x</button>
                </div>
            </div>
            <div class="window-body">
                <div id="memory-game">
                    <div id="game-board"></div>
                    <div id="game-info">
                        <p>Time : <span id="timer">00:00</span></p>
                        <p id="memory-result"></p>
                        <button id="reset-btn" type="button">Restart</button>
                        <button type="button" class="return-btn" data-close="window-memory">Return</button>
                    </div>
                </div>
            </div>
        </section>

        <!-- SHIFUMI GAME WINDOW -->
        <section class="window" id="window-shifumi" aria-labelledby="title-shifumi" hidden>
            <div class="title-bar">
                <span id="title-shifumi" class="title-bar-text">SHIFUMI.EXE</span>
                <div class="title-bar-buttons">
                    <button type="button" class="title-bar-button btn-close" data-close="window-shifumi" aria-label="Close ShiFuMi">✕</button>
                </div>
            </div>
            <div class="window-body">
                <div id="shifumi-game">
                    <div id="play-buttons">
                        <button class="btn" data-move="rock" type="button">rock</button>
                        <button class="btn" data-move="paper" type="button">paper</button>
                        <button class="btn" data-move="scissors" type="button">scissors</button>
                    </div>

                    <p id="shifumi-result" aria-live="polite"></p>

                    <div id="stats">
                        <p>victories : <span class="stat" id="victories">0</span></p>
                        <p>defeats : <span class="stat" id="defeats">0</span></p>
                        <p>nulls : <span class="stat" id="nulls">0</span></p>
                    </div>

                    <div id="control-buttons">
                        <button class="reset" id="reset" type="button">reset</button>
                        <button type="button" class="return-btn" data-close="window-shifumi">Return</button>
                    </div>
                </div>
            </div>
        </section>

        <!-- DYNAMIC APPS WINDOWS (desktop-apps/) -->
        <?php foreach ($apps as $app): ?>
            <section class="window" id="<?= htmlspecialchars($app['target']) ?>" aria-labelledby="title-<?= htmlspecialchars($app['id']) ?>" hidden>
                <div class="title-bar">
                    <span id="title-<?= htmlspecialchars($app['id']) ?>" class="title-bar-text"><?= htmlspecialchars($app['title']) ?></span>
                    <div class="title-bar-buttons">
                        <button type="button" class="title-bar-button btn-close" data-close="<?= htmlspecialchars($app['target']) ?>" aria-label="Close">x</button>
                    </div>
                </div>
                <div class="window-body">
                    <?php if (!empty($app['content'])): ?>
                        <?php include $app['content']; ?>
                    <?php else: ?>
                        <p>Program loaded: <?= htmlspecialchars($app['title']) ?></p>
                    <?php endif; ?>
                </div>
            </section>
        <?php endforeach; ?>
    </main>

    <!-- TASKBAR & START MENU -->
    <footer class="taskbar">
        <button type="button" id="start-menu-btn" class="start-menu-btn" aria-haspopup="true" aria-expanded="false" aria-controls="start-menu">
            Start
        </button>

        <div class="start-menu" id="start-menu" hidden>
            <div class="start-menu-sidebar"></div>
            <ul class="start-menu-apps" id="start-menu-apps">
                <li>
                    <button type="button" class="start-menu-item" data-target="window-explorer">
                        Games
                    </button>
                </li>
                <li>
                    <button type="button" class="start-menu-item" data-target="window-filemanager">
                        Files
                    </button>
                </li>
                <?php foreach ($apps as $app): ?>
                    <li>
                        <button type="button" class="start-menu-item" data-target="<?= htmlspecialchars($app['target']) ?>">
                            <?= htmlspecialchars($app['title']) ?>
                        </button>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="taskbar-items"></div>
    </footer>
</body>
</html>
