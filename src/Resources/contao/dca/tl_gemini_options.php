<?php

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Contao\DC_Table;

$GLOBALS['TL_DCA']['tl_gemini_options'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'enableVersioning' => true,
        'sql' => [
            'keys' => [
                'id' => 'primary'
            ]
        ]
    ],
    'list' => [
        'sorting' => [
            'mode' => 1,
            'fields' => ['name'],
            'panelLayout' => 'filter;sort,search,limit'
        ],
        'label' => [
            'fields' => ['name'],
            'showColumns' => true
        ],
        'operations' => [
            'edit',
            'delete',
            'show'
        ],
    ],
    'palettes' => [
        'default' => 'name;model,aspectRatio;prompt;references'
    ],
    'fields' => [
        'id' => [
            'sql' => ['type' => 'integer', 'autoincrement' => true, 'notnull' => true, 'unsigned' => true]
        ],
        'tstamp' => [
            'sql' => ['type' => 'integer', 'notnull' => false, 'unsigned' => true, 'default' => 0]
        ],
        'name' => [
            'inputType' => 'text',
            'eval' => [
                'maxlength' => 128,
                'tl_class' => 'w50',
                'mandatory' => true,
                'decodeEntities' => true
            ],
            'search' => true,
            'sql' => ['type' => 'string', 'length' => 128, 'default' => '']
        ],
        'model' => [
            'inputType' => 'select',
            'eval' => [
                'maxlength' => 32,
                'tl_class' => 'w50',
                'mandatory' => true
            ],
            'filter' => true,
            'options' => ['gemini-2.5-flash-image', 'gemini-3.1-flash-image-preview'],
            'sql' => ['type' => 'string', 'length' => 32, 'default' => '']
        ],
        'aspectRatio' => [
            'inputType' => 'select',
            'eval' => [
                'maxlength' => 12,
                'tl_class' => 'w50',
                'mandatory' => true,
                'decodeEntities' => true
            ],
            'options' => ['16:9', '4:3', '1:1', '2:3'],
            'sql' => ['type' => 'string', 'length' => 16, 'default' => '']
        ],
        'prompt' => [
            'inputType' => 'textarea',
            'eval' => [
                'tl_class' => 'clr',
                'mandatory' => true,
                'decodeEntities' => true
            ],
            'sql' => 'text NULL'
        ],
        'references' => [
            'inputType' => 'rowWizard',
            'eval' => [
                'tl_class' => 'clr w50',
                'mandatory' => false,
                'actions' => [
                    'copy',
                    'delete'
                ]
            ],
            'fields' => [
                'image' => [
                    'label' => &$GLOBALS['TL_LANG']['tl_gemini_options']['image'],
                    'inputType' => 'fileTree',
                    'eval' => [
                        'multiple' => false,
                        'fieldType' => 'radio',
                        'files' => true,
                        'filesOnly' => true,
                        'extensions' => ($GLOBALS['TL_CONFIG']['validImageTypes'] ?? '')
                    ]
                ]
            ],
            'sql' => [
                'type' => 'blob',
                'length' => AbstractMySQLPlatform::LENGTH_LIMIT_BLOB,
                'notnull' => false,
            ]
        ]
    ]
];