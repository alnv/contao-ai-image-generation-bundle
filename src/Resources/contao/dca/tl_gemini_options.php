<?php

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Contao\DC_Table;
use Contao\DataContainer;

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
        '__selector__' => ['type'],
        'default' => 'type',
        'image' => 'type,name;model,aspectRatio;prompt;references',
        'video' => 'type,subType,name;model;prompt;references'
    ],
    'fields' => [
        'id' => [
            'sql' => ['type' => 'integer', 'autoincrement' => true, 'notnull' => true, 'unsigned' => true]
        ],
        'tstamp' => [
            'sql' => ['type' => 'integer', 'notnull' => false, 'unsigned' => true, 'default' => 0]
        ],
        'type' => [
            'inputType' => 'select',
            'eval' => [
                'maxlength' => 16,
                'tl_class' => 'w50',
                'mandatory' => true,
                'submitOnChange' => true
            ],
            'filter' => true,
            'reference' => &$GLOBALS['TL_LANG']['tl_gemini_options'],
            'options' => ['video', 'image'],
            'sql' => ['type' => 'string', 'length' => 16, 'default' => 'image']
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
            'options_callback' => function (DataContainer $dc) {
                $type = $dc->getCurrentRecord()['type'];
                if ($type == 'image') {
                    return ['gemini-2.5-flash-image', 'gemini-3.1-flash-image-preview', 'gemini-3.1-flash-image'];
                }

                if ($type == 'video') {
                    return ['veo-3.1-generate-preview'];
                }

                return [];
            },
            'sql' => ['type' => 'string', 'length' => 32, 'default' => '']
        ],
        'subType' => [
            'inputType' => 'select',
            'eval' => [
                'maxlength' => 32,
                'tl_class' => 'w50',
                'mandatory' => true,
                'includeBlankOption' => true
            ],
            'filter' => true,
            'options_callback' => function (DataContainer $dc) {
                $type = $dc->getCurrentRecord()['type'];
                /*
                if ($type == 'image') {
                    return ['image-to-image'];
                }
                */
                if ($type == 'video') {
                    return ['image-to-video', 'images-to-image', 'frames-to-image'];
                }

                return [];
            },
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