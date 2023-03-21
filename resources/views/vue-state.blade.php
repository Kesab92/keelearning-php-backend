<script>
    window.VUEX_STATE = {
        languages: {
            languages: {!! json_encode(appLanguages()) !!},
            activeLanguage: "{{ language() }}",
        },
        appId: {{ appId() }},
        relaunchBackendUIUrl: "{{ env('RELAUNCH_BACKEND_UI_URL') }}"
    }
</script>
