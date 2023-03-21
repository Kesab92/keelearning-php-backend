<div class="ui left vertical labeled icon sidebar visible menu">
    <div class="menu">
        @if(isSuperAdmin())
            <div class="ui header-item clearfix">
                <a href="/appswitcher#/appswitcher" class="s-mainAppTitle" data-variation="inverted" data-position="right center">
                    <img class="app-logo" src="{{ $settings->getApp()->logo_url }}">
                    <h3 class="app-name">{{ $settings->getApp()->app_name }}</h3>
                    <i class="exchange icon"></i>
                </a>
            </div>
        @else
            <div class="header-item clearfix">
                <img class="app-logo" src="{{ $settings->getApp()->logo_url }}">
                <h3 class="app-name">{{ $settings->getApp()->app_name }}</h3>
            </div>
        @endif

        <div class="section">Home</div>
        <ul>
            <li class="@if($activeNav == 'dashboard') active-nav @endif">
                <a href="/">
                    <span class="nav-icon">
                        <i class="dashboard icon"></i>
                    </span>
                    <span class="nav-title">Dashboard</span>
                </a>
            </li>
            @if($user->hasRight('users-edit') || $user->hasRight('users-view'))
                <li class="@if($activeNav == 'users') active-nav @endif">
                    <a href="/users#/users">
                        <span class="nav-icon">
                            <i class="user icon"></i>
                        </span>
                        <span class="nav-title">Benutzer</span>
                    </a>
                </li>
            @endif
        </ul>

        @if (
            ($user->hasRight('questions-edit'))
            || $user->hasRight('categories-edit')
            || ($settings->isBackendVisible('courses') && ($user->hasRight('courses-edit') || $user->hasRight('courses-view')))
            || ($settings->isBackendVisible('forms') && $user->hasRight('forms-edit'))
            || ($settings->isBackendVisible('index_cards') && $user->hasRight('indexcards-edit'))
            || ($settings->isBackendVisible('questions') && $user->hasRight('questions-edit'))
            || ($settings->isBackendVisible('suggested_questions') && $user->hasRight('suggestedquestions-edit'))
            || ($settings->isBackendVisible('competitions') && $user->hasRight('competitions-edit'))
            || ($settings->isBackendVisible('learningmaterials') && $user->hasRight('learningmaterials-edit'))
            || ($settings->isBackendVisible('quiz') && $user->hasRight('quizteams-personaldata'))
            || ($settings->isBackendVisible('keywords') && $user->hasRight('keywords-edit'))
            || ($settings->isBackendVisible('tests') && ($user->hasRight('tests-edit') || $user->hasRight('tests-view')))
        )
            <div class="section">Content</div>
            <?php $isQuizLearningOpen = in_array($activeNav, [
                'categories',
                'competitions',
                'quiz-teams',
                'questions',
                'suggestedQuestions',
            ]); ?>
            <ul>
                @if($user->hasRight('questions-edit'))
                    <li>
                        <a href="/questions#/questions?create">
                            <span class="nav-icon">
                                <i class="question circle icon"></i>
                            </span>
                            <span class="nav-title">Frage erstellen</span>
                        </a>
                    </li>
                @endif
                @if (
                    $user->hasRight('categories-edit')
                    || ($settings->isBackendVisible('questions') && $user->hasRight('questions-edit'))
                    || ($settings->isBackendVisible('suggested_questions') && $user->hasRight('suggestedquestions-edit'))
                    || ($settings->isBackendVisible('competitions') && $user->hasRight('competitions-edit'))
                    || ($settings->isBackendVisible('quiz') && $user->hasRight('quizteams-personaldata'))
                )
                    <li class="submenu @if($isQuizLearningOpen) active-submenu @endif">
                        <span class="nav-icon">
                            <i class="tags icon"></i>
                        </span>
                        <span class="nav-title">Quiz & Powerlearning</span>
                        <span class="submenu-icon">
                            <i class="caret down icon"></i>
                        </span>
                        <ul class="submenu-entries" @if($isQuizLearningOpen) style="display: block;" @endif>
                            @if($user->hasRight('categories-edit'))
                                <li class="@if($activeNav == 'categories') active-nav @endif">
                                    <a href="/categories">
                                        <span class="nav-title">Lernkategorien</span>
                                    </a>
                                </li>
                            @endif
                            @if($settings->isBackendVisible('questions') && $user->hasRight('questions-edit'))
                                <li class="@if($activeNav == 'questions') active-nav @endif">
                                    <a href="/questions#/questions">
                                        <span class="nav-title">Lernfragen-Pool</span>
                                    </a>
                                </li>
                            @endif
                            @if($settings->isBackendVisible('quiz') && $user->hasRight('quizteams-personaldata'))
                                <li class="@if($activeNav == 'quiz-teams') active-nav @endif">
                                    <a href="/quiz-teams#/quiz-teams">
                                        <span class="nav-title">Quiz-Teams</span>
                                    </a>
                                </li>
                            @endif
                            @if($settings->isBackendVisible('competitions') && $user->hasRight('competitions-edit'))
                                <li class="@if($activeNav == 'competitions') active-nav @endif">
                                    <a href="/competitions">
                                        <span class="nav-title">Quiz-Gewinnspiele</span>
                                    </a>
                                </li>
                            @endif
                            @if($settings->isBackendVisible('suggested_questions') && $user->hasRight('suggestedquestions-edit'))
                                <li class="@if($activeNav == 'suggestedQuestions') active-nav @endif">
                                    <a href="/suggested-questions#/suggested-questions">User: Eingereichte Fragen</a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif
                @if($settings->isBackendVisible('learningmaterials') && $user->hasRight('learningmaterials-edit'))
                    <li class="@if($activeNav == 'learningmaterials') active-nav @endif">
                        <a href="/learningmaterials?#/learningmaterials">
                            <span class="nav-icon">
                                @include('layout.partials.icon', ['type' => 'cards'])
                            </span>
                            <span class="nav-title">Mediathek</span>
                        </a>
                    </li>
                @endif
                @if($settings->isBackendVisible('courses') && ($user->hasRight('courses-edit') || $user->hasRight('courses-view')))
                    <li class="@if($activeNav == 'courses') active-nav @endif">
                        <a href="/courses#/courses">
                        <span class="nav-icon">
                            @include('layout.partials.icon', ['type' => 'tasks'])
                        </span>
                            <span class="nav-title">Kurse</span>
                        </a>
                    </li>
                @endif
                @if($settings->isBackendVisible('index_cards') && $user->hasRight('indexcards-edit'))
                    <li class="@if($activeNav == 'indexcards') active-nav @endif">
                        <a href="/indexcards">
                            <span class="nav-icon">
                                @include('layout.partials.icon', ['type' => 'stack'])
                            </span>
                            <span class="nav-title">Karteikarten</span>
                        </a>
                    </li>
                @endif
                @if($settings->isBackendVisible('keywords') && $user->hasRight('keywords-edit'))
                    <li class="@if($activeNav == 'keywords') active-nav @endif">
                        <a href="/keywords#/keywords">
                    <span class="nav-icon">
                        <i class="book icon"></i>
                    </span>
                            <span class="nav-title">Schlagwörter</span>
                        </a>
                    </li>
                @endif
                @if($settings->isBackendVisible('tests') && ($user->hasRight('tests-edit') || $user->hasRight('tests-view')))
                    <li class="@if($activeNav == 'tests') active-nav @endif">
                        <a href="/tests#/tests">
                            <span class="nav-icon">
                                @include('layout.partials.icon', ['type' => 'grade'])
                            </span>
                            <span class="nav-title">Tests</span>
                        </a>
                    </li>
                @endif
                @if($settings->isBackendVisible('forms') && $user->hasRight('forms-edit'))
                    <li class="@if($activeNav == 'forms') active-nav @endif">
                        <a href="/forms#/forms">
                            <span class="nav-icon">
                                    <i class="table icon"></i>
                            </span>
                            <span class="nav-title">Formulare</span>
                        </a>
                    </li>
                @endif
            </ul>
        @endif

        @if (
            $user->hasRight('tags-edit')
            || ($settings->isBackendVisible('vouchers') && $user->hasRight('vouchers-edit'))
            || ($user->hasRight('mails-edit'))
            || ($settings->isBackendVisible('advertisements') && $user->hasRight('advertisements-edit'))
            || ($user->hasRight('settings-edit'))
            || ($user->hasRight('pages-edit'))
            || ($user->hasRight('questions-edit') && $settings->getValue('import_questions'))
            || ($user->hasRight('indexcards-edit') && $settings->getValue('import_index_cards'))
            || ($user->hasRight('users-edit') && $settings->getValue('import_users'))
            || ($user->hasRight('users-edit') && $settings->getValue('import_users_delete'))
        )
            <div class="section">Konfiguration</div>
            <ul>
                @if($user->hasRight('tags-edit'))
                    @if (!$settings->isBackendVisible('tag_groups'))
                        <li class="@if($activeNav == 'tags') active-nav @endif">
                            <a href="/tags#/tags">
                                <span class="nav-icon">
                                    <i class="tag icon"></i>
                                </span>
                                <span class="nav-title">Benutzergruppen / TAGs</span>
                            </a>
                        </li>
                    @else
                        <?php $isTagsOpen = in_array($activeNav, ['tags', 'tag_groups']); ?>
                        <li class="submenu @if($isTagsOpen) active-submenu @endif">
                            <span class="nav-icon">
                                <i class="tags icon"></i>
                            </span>
                            <span class="nav-title">Benutzergruppen / TAGs</span>
                                <span class="submenu-icon">
                                <i class="caret down icon"></i>
                            </span>
                            <ul class="submenu-entries" @if($isTagsOpen) style="display: block;" @endif>
                                <li class="@if($activeNav == 'tags') active-nav @endif">
                                    <a href="/tags#/tags">TAGs</a>
                                </li>
                                <li class="@if($activeNav == 'tag_groups') active-nav @endif">
                                    <a href="/tag-groups">TAG-Gruppen</a>
                                </li>
                            </ul>
                        </li>
                    @endif
                @endif
                @if ($settings->isBackendVisible('vouchers') && $user->hasRight('vouchers-edit'))
                    <li class="@if($activeNav == 'vouchers') active-nav @endif">
                        <a href="/vouchers#/vouchers">
                            <span class="nav-icon">
                                <i class="ticket alternate icon"></i>
                            </span>
                            <span class="nav-title">Vouchers</span>
                        </a>
                    </li>
                @endif
                @if($user->hasRight('mails-edit'))
                    <li class="@if($activeNav == 'mails') active-nav @endif">
                        <a href="/mails">
                            <span class="nav-icon">
                                <i class="mail outline icon"></i>
                            </span>
                            <span class="nav-title">E-Mail-Vorlagen</span>
                        </a>
                    </li>
                @endif
                @if($settings->isBackendVisible('advertisements') && $user->hasRight('advertisements-edit'))
                    <li class="@if($activeNav == 'advertisements') active-nav @endif">
                        <a href="/advertisements#/advertisements">
                        <span class="nav-icon">
                            <i class="bullhorn icon"></i>
                        </span>
                            <span class="nav-title">Banner</span>
                        </a>
                    </li>
                @endif
                @if($user->hasRight('settings-edit'))
                    <li class="@if($activeNav == 'settings') active-nav @endif">
                        <a href="/settings#/settings/{{ $settings->getApp()->getDefaultAppProfile()->id }}/admin.options">
                            <span class="nav-icon">
                                <i class="setting icon"></i>
                            </span>
                            <span class="nav-title">Einstellungen</span>
                        </a>
                    </li>
                @endif
                @if($user->hasRight('pages-edit'))
                    <li class="@if($activeNav == 'pages') active-nav @endif">
                        <a href="/pages#/pages">
                            <span class="nav-icon">
                                @include('layout.partials.icon', ['type' => 'info'])
                            </span>
                            <span class="nav-title">Seiten</span>
                        </a>
                    </li>
                @endif
                @if(
                    ($user->hasRight('questions-edit') && $settings->getValue('import_questions'))
                    || ($user->hasRight('indexcards-edit') && $settings->getValue('import_index_cards'))
                    || ($user->hasRight('users-edit') && $settings->getValue('import_users'))
                    || ($user->hasRight('users-edit') && $settings->getValue('import_users_delete'))
                )
                    <li class="@if($activeNav == 'import') active-nav @endif">
                        <a href="/import">
                            <span class="nav-icon">
                                <i class="upload icon"></i>
                            </span>
                            <span class="nav-title">Import</span>
                        </a>
                    </li>
                @endif
            </ul>
        @endif

        @if(
            ($settings->isBackendVisible('news') && $user->hasRight('news-edit'))
            || ($settings->isBackendVisible('webinars') && $user->hasRight('webinars-personaldata'))
            || ($settings->isBackendVisible('comments') && $user->hasRight('comments-personaldata'))
            || ($settings->isBackendVisible('appointments') && ($user->hasRight('appointments-view') || $user->hasRight('appointments-edit')))
        )
            <div class="section">Kommunikation</div>
            <ul>
                @if($settings->isBackendVisible('comments') && $user->hasRight('comments-personaldata'))
                    <li class="@if($activeNav == 'comments') active-nav @endif">
                        <a href="/comments#/comments">
                            <span class="nav-icon">
                                <i class="chat icon"></i>
                            </span>
                            <span class="nav-title">Kommentare</span>
                        </a>
                    </li>
                @endif
                @if($settings->isBackendVisible('news') && $user->hasRight('news-edit'))
                    <li class="@if($activeNav == 'news') active-nav @endif">
                        <a href="/news#/news">
                            <span class="nav-icon">
                                @include('layout.partials.icon', ['type' => 'speaker'])
                            </span>
                            <span class="nav-title">News</span>
                        </a>
                    </li>
                @endif
                @if ($settings->isBackendVisible('webinars') && $user->hasRight('webinars-personaldata'))
                    <li class="@if($activeNav == 'webinars') active-nav @endif">
                        <a href="/webinars">
                            <span class="nav-icon">
                                @include('layout.partials.icon', ['type' => 'user-headset'])
                            </span>
                            <span class="nav-title">Webinare</span>
                        </a>
                    </li>
                @endif
                @if ($settings->isBackendVisible('appointments') && ($user->hasRight('appointments-view') || $user->hasRight('appointments-edit')))
                    <li class="@if($activeNav == 'appointments') active-nav @endif">
                        <a href="/appointments#/appointments">
                        <span class="nav-icon">
                            <i class="calendar alternate outline icon"></i>
                        </span>
                            <span class="nav-title">Termine</span>
                        </a>
                    </li>
                @endif
            </ul>
        @endif

        @if (
            ($settings->isBackendVisible('stats_users') && $user->hasRight('users-stats'))
            || ($settings->isBackendVisible('stats_quiz_challenge') && $settings->isBackendVisible('quiz') && $user->hasRight('questions-stats'))
            || ($settings->isBackendVisible('stats_training') && $user->hasRight('questions-stats'))
            || ($settings->isBackendVisible('stats_wbt') && $settings->getValue('wbt_enabled') && $user->hasRight('learningmaterials-stats'))
            || ($settings->isBackendVisible('stats_views') && $user->hasRight('settings-viewcounts'))
            || ($user->hasRight('settings-ratings'))
        )
            <?php $isStatisticsOpen = in_array($activeNav, [
                'stats.quiz',
                'stats.training',
                'stats.wbt',
                'stats.views',
                'stats.ratings',
                'stats.users',
                'superadmin.useractivity',
            ]); ?>
            <div class="section submenu  @if($isStatisticsOpen) active-submenu @endif">
                Statistiken
                <span class="submenu-icon">
                    <i class="caret down icon"></i>
                </span>
            </div>
            <ul @if(!$isStatisticsOpen) style="display: none" @endif>
                @if ($settings->isBackendVisible('stats_users') && $user->hasRight('users-stats'))
                    <li class="@if($activeNav == 'stats.users') active-nav @endif">
                        <a href="/stats/users#/stats/users">
                            <span class="nav-icon">
                                <i class="user icon"></i>
                            </span>
                            <span class="nav-title">Benutzer</span>
                        </a>
                    </li>
                @endif
                @if ($settings->isBackendVisible('stats_quiz_challenge') && $settings->isBackendVisible('quiz') && $user->hasRight('questions-stats'))
                    <li class="@if($activeNav == 'stats.quiz') active-nav @endif">
                        <a href="/stats/quiz#/stats/quiz/players">
                            <span class="nav-icon">
                                @include('layout.partials.icon', ['type' => 'trophy'])
                            </span>
                            <span class="nav-title">Quiz-Battle</span>
                        </a>
                    </li>
                @endif
                @if ($settings->isBackendVisible('stats_training') && $user->hasRight('questions-stats'))
                    <li class="@if($activeNav == 'stats.training') active-nav @endif">
                        <a href="/stats/training">
                            <span class="nav-icon">
                                @include('layout.partials.icon', ['type' => 'checkbox'])
                            </span>
                            <span class="nav-title">{{ \App\Models\App::find(appId())->usePowerLearning() ? 'Powerlearning' : 'Training' }}</span>
                        </a>
                    </li>
                @endif
                @if ($settings->isBackendVisible('stats_wbt') && $settings->getValue('wbt_enabled') && $user->hasRight('learningmaterials-stats'))
                    <li class="@if($activeNav == 'stats.wbt') active-nav @endif">
                        <a href="/stats/wbt">
                            <span class="nav-icon">
                                <i class="sitemap icon"></i>
                            </span>
                            <span class="nav-title">WBT Statistik</span>
                        </a>
                    </li>
                @endif
                @if ($settings->isBackendVisible('stats_views') && $user->hasRight('settings-viewcounts'))
                    <li class="@if($activeNav == 'stats.views') active-nav @endif">
                        <a href="/stats/views">
                            <span class="nav-icon">
                                <i class="eye icon"></i>
                            </span>
                            <span class="nav-title">Aufrufe</span>
                        </a>
                    </li>
                @endif
                @if ($user->hasRight('settings-ratings'))
                    <li class="@if($activeNav == 'stats.ratings') active-nav @endif">
                        <a href="/stats/ratings">
                            <span class="nav-icon">
                                <i class="star icon"></i>
                            </span>
                            <span class="nav-title">Bewertungen</span>
                        </a>
                    </li>
                @endif
                @if ($user->isSuperAdmin())
                    <li class="@if($activeNav == 'superadmin.useractivity') active-nav @endif">
                        <a href="/superadmin/user-activity#/superadmin/user-activity">
                            <span class="nav-icon">
                                <i class="chart line icon"></i>
                            </span>
                            <span class="nav-title">User-Aktivität</span>
                        </a>
                    </li>
                @endif
            </ul>
        @endif
    </div>
</div>
