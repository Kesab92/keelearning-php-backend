<header>
    <a class="header-item ui hollow basic button" href="{{ \App\Models\App::find(appId())->getDefaultAppProfile()->app_hosted_at }}"
       target="_blank">
        App öffnen
    </a>
    <div class="profile-section header-item">
        @if(App\Models\App::find(appId())->support_phone_number)
            <span style="margin-right: 20px;">
                Telefonsupport:
                {{ App\Models\App::find(appId())->support_phone_number }}
            </span>
        @endif
        <a class="header-helpdesk" target="_blank" href="http://helpdesk.keelearning.de/">Helpdesk</a>
        Hallo {{ Auth::user()->username }} | <a href="/logout">Logout</a>
    </div>
</header>
