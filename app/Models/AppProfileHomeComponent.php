<?php

namespace App\Models;

use App\Models\Advertisements\Advertisement;

/**
 * @mixin IdeHelperAppProfileHomeComponent
 */
class AppProfileHomeComponent extends KeelearningModel
{
    protected $casts = [
        'settings' => 'array',
    ];

    const BLUEPRINTS = [
        'news' => [
            'name' => 'News',
            'icon' => '<svg aria-hidden="true" class="c-icon" focusable="false"><use xlink:href="#speaker"></use></svg>',
            'module' => 'module_news',
            'unique' => true,
        ],
        'appmobileinstallation' => [
            'name' => 'Hinweis auf die Store App (nur auf dem Handy sichtbar)',
            'icon' => '<i class="mobile alternate icon"></i>',
            'module' => null,
            'unique' => true,
        ],
        'appointments' => [
            'name' => 'Termine',
            'icon' => '<i class="calendar alternate outline icon"></i>',
            'module' => 'module_appointments',
            'unique' => true,
        ],
        'quiz' => [
            'name' => 'Quiz',
            'icon' => '<i class="tags icon"></i>',
            'module' => 'module_quiz',
            'unique' => true,
        ],
        'competitions' => [
            'name' => 'Gewinnspiele',
            'icon' => '<i class="trophy icon"></i>',
            'module' => 'module_competitions',
            'unique' => true,
        ],
        'advertisements' => [
            'name' => 'Banner',
            'icon' => '<i class="bullhorn icon"></i>',
            'module' => 'module_advertisements',
            'unique' => false,
            'settings' => [
                'position' => [
                    'type' => 'select',
                    'label' => 'Position',
                    'options' => [
                        [
                            'text' => 'Mitte',
                            'value' => Advertisement::POSITIONS['POSITION_HOME_MIDDLE'],
                        ],
                        [
                            'text' => 'Unten',
                            'value' => Advertisement::POSITIONS['POSITION_HOME_BOTTOM'],
                        ],
                    ],
                    'default' => Advertisement::POSITIONS['POSITION_HOME_MIDDLE'],
                ],
            ],
        ],
        'powerlearning' => [
            'name' => 'Powerlearning',
            'icon' => '<i class="tags icon"></i>',
            'module' => 'module_powerlearning',
            'unique' => true,
        ],
        'learningmaterials' => [
            'name' => 'Mediathek',
            'icon' => '<svg aria-hidden="true" class="c-icon" focusable="false"><use xlink:href="#cards"></use></svg>',
            'module' => 'module_learningmaterials',
            'unique' => true,
        ],
        'courses' => [
            'name' => 'Kurse',
            'icon' => '<svg aria-hidden="true" class="c-icon" focusable="false"><use xlink:href="#tasks"></use></svg>',
            'module' => 'module_courses',
            'unique' => true,
            'settings' => [
                'rows' => [
                    'type' => 'number',
                    'label' => 'Maximale Anzahl an Zeilen',
                    'hint' => 'Hier können Sie einschränken wie viele Zeilen mit Kurs-Karten angezeigt werden',
                    'min' => 1,
                    'step' => 1,
                    'default' => 2,
                ],
            ],
        ],
        'tests' => [
            'name' => 'Tests',
            'icon' => '<svg aria-hidden="true" class="c-icon" focusable="false"><use xlink:href="#grade"></use></svg>',
            'module' => 'module_tests',
            'unique' => true,
        ],
        'challengingquestions' => [
            'name' => 'Schwierige Fragen',
            'icon' => '<i class="question circle icon"></i>',
            'module' => 'module_quiz',
            'unique' => true,
        ],
    ];

    const DEFAULT_COMPONENTS = [
        [
            'type' => 'news',
            'visible' => true,
        ],
        [
            'type' => 'appmobileinstallation',
            'visible' => true,
        ],
        [
            'type' => 'appointments',
            'visible' => true,
        ],
        [
            'type' => 'quiz',
            'visible' => true,
        ],
        [
            'type' => 'competitions',
            'visible' => true,
        ],
        [
            'type' => 'advertisements',
            'settings' => ['position' => Advertisement::POSITIONS['POSITION_HOME_MIDDLE']],
            'visible' => true,
        ],
        [
            'type' => 'powerlearning',
            'visible' => true,
        ],
        [
            'type' => 'learningmaterials',
            'visible' => true,
        ],
        [
            'type' => 'courses',
            'visible' => true,
        ],
        [
            'type' => 'tests',
            'visible' => true,
        ],
        [
            'type' => 'advertisements',
            'settings' => ['position' => Advertisement::POSITIONS['POSITION_HOME_BOTTOM']],
            'visible' => true,
        ],
        [
            'type' => 'challengingquestions',
            'visible' => true,
        ],
    ];

    public function appProfile()
    {
        return $this->belongsTo(AppProfile::class);
    }
}
