<?php

return [
    // String, required, root directory of all source files
    'sourcePath' => __DIR__ . DIRECTORY_SEPARATOR . '..',
    // Array, required, list of language codes that the extracted messages
    'languages' => ['uk', 'ru'],
    // String or array of strings, the name of the function for translating messages.
    'translator' => 'Yii::t',
    // Boolean, whether to sort messages by keys when merging new messages
    'sort' => true,
    // Boolean, whether to remove messages that no longer appear in the source code.
    'removeUnused' => false,
    // Array, list of patterns that specify which files (not directories) should be processed.
    'only' => ['*.php'],
    // Array, list of patterns that specify which files/directories should NOT be processed.
    'except' => [
        '.svn',
        '.git',
        '.gitignore',
        '.gitkeep',
        '.hgignore',
        '.hgkeep',
        '/messages',
    ],
    // 'php' output format is for saving messages to php files.
    'format' => 'php',
    // Root directory containing message translations.
    'messagePath' => __DIR__,
    // Boolean, whether the message file should be overwritten with the merged messages
    'overwrite' => true,
    // Message categories to ignore
    'ignoreCategories' => [
        'yii', 'user', 'post', 'ad'
    ],
];
