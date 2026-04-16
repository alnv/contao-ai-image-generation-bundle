<?php

use Contao\ArrayUtil;

ArrayUtil::arrayInsert($GLOBALS['BE_MOD'], 2, [
    'ai-image-generation-bundle' => [
        'gemini_options' => [
            'name' => 'gemini_options',
            'tables' => [
                'tl_gemini_options'
            ]
        ]
    ]
]);