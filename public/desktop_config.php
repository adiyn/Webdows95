<?php
declare(strict_types=1);

function resolve_file_icon(string $filename): string {
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    return match ($extension) {
        'mid', 'wav', 'mp3', 'ogg'          => './img/Audio File.webp',
        'jpg', 'jpeg', 'png', 'gif', 'webp' => './img/Display.png',
        'raw', 'dmp'                        => './img/BSOD.jpg',
        default                             => './img/File.webp',
    };
}

$paths = [
    'files' => __DIR__ . '/files',
    'apps'  => __DIR__ . '/desktop-apps',
    'games' => __DIR__ . '/js/games',
];

foreach ($paths as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
}

$virtualFiles = [];
$games        = [];
$apps         = [];

$hardcodedIcons = [
    [
        'target' => 'window-explorer',
        'icon'   => './img/Games.png',
        'label'  => 'Games',
    ],
    [
        'target' => 'window-filemanager',
        'icon'   => './img/Folder.webp',
        'label'  => 'Files',
    ],
    [
        'target' => null,
        'icon'   => './img/Empty Recycle Bin.webp',
        'label'  => 'Recycle Bin',
    ],
];

$rawFiles = array_diff(scandir($paths['files']) ?: [], ['.', '..']);
foreach ($rawFiles as $item) {
    if (str_ends_with($item, '.json') || is_dir($paths['files'] . '/' . $item)) {
        continue;
    }

    $jsonPath = $paths['files'] . '/' . $item . '.json';
    if (!file_exists($jsonPath)) {
        $meta = [
            'name'      => $item,
            'icon'      => resolve_file_icon($item),
            'extension' => pathinfo($item, PATHINFO_EXTENSION),
            'size'      => @filesize($paths['files'] . '/' . $item) ?: 0,
        ];
        @file_put_contents($jsonPath, json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    $fileData = json_decode((string) @file_get_contents($jsonPath), true) ?: [];
    $virtualFiles[] = [
        'name' => $fileData['name'] ?? $item,
        'icon' => $fileData['icon'] ?? resolve_file_icon($item),
    ];
}

$rawApps = array_diff(scandir($paths['apps']) ?: [], ['.', '..']);
foreach ($rawApps as $item) {
    if (str_ends_with($item, '.json')) {
        continue;
    }

    $slug     = strtolower(pathinfo($item, PATHINFO_FILENAME));
    $jsonPath = $paths['apps'] . '/' . $slug . '.json';

    if (!file_exists($jsonPath)) {
        $meta = [
            'id'     => $slug,
            'label'  => ucfirst($slug),
            'target' => 'window-' . $slug,
            'icon'   => './img/Display.png',
            'file'   => $item,
        ];
        @file_put_contents($jsonPath, json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}

$dynamicAppIcons = [];
$appDescriptors = glob($paths['apps'] . '/*.json') ?: [];
foreach ($appDescriptors as $jsonPath) {
    $appData = json_decode((string) @file_get_contents($jsonPath), true);
    if ($appData && !empty($appData['label'])) {
        $slug = $appData['id'] ?? strtolower(pathinfo($jsonPath, PATHINFO_FILENAME));
        $target = $appData['target'] ?? ('window-' . $slug);

        $dynamicAppIcons[] = [
            'target' => $target,
            'icon'   => $appData['icon'] ?? './img/Display.png',
            'label'  => $appData['label'],
        ];

        $apps[] = [
            'id'      => $slug,
            'target'  => $target,
            'title'   => strtoupper($appData['label']) . '.EXE',
            'content' => isset($appData['file']) && file_exists($paths['apps'] . '/' . $appData['file'])
                ? $paths['apps'] . '/' . $appData['file']
                : null,
        ];
    }
}

$gameScripts = glob($paths['games'] . '/*.js') ?: [];
foreach ($gameScripts as $scriptPath) {
    $gameSlug = strtolower(pathinfo($scriptPath, PATHINFO_FILENAME));
    $jsonPath = $paths['games'] . '/' . $gameSlug . '.json';

    if (!file_exists($jsonPath)) {
        $meta = [
            'id'     => $gameSlug,
            'target' => 'window-' . $gameSlug,
            'label'  => strtoupper($gameSlug) . '.EXE',
            'script' => './js/games/' . basename($scriptPath),
        ];
        @file_put_contents($jsonPath, json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    $gameData = json_decode((string) @file_get_contents($jsonPath), true);
    if ($gameData) {
        $games[] = $gameData;
    }
}

$allIcons = array_merge($hardcodedIcons, $dynamicAppIcons);
$desktopIcons = array_map(function ($icon, $index) {
    $icon['top'] = $index * 80;
    return $icon;
}, $allIcons, array_keys($allIcons));

return [
    'desktopIcons' => $desktopIcons,
    'virtualFiles' => $virtualFiles,
    'games'        => $games,
    'apps'         => $apps,
];