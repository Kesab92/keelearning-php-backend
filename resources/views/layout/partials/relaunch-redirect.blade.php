<script>
    if (window.parent && window.location !== window.parent.location) {
        document.body.classList.add('js-isInFrame')
    } else {
        if(localStorage.getItem('showBackendV1') === '1') {
            document.body.classList.add('js-showBackendV1')
        } else {
            document.documentElement.style.overflow = "hidden"
            if(window.location.href.match(/^https:\/\/admin\./)) {
                window.setTimeout(() => {
                    window.location.href = window.location.href.replace(/^https:\/\/admin\./, 'https://myadmin.')
                }, 5000)
            }
        }
    }

</script>
<style>
    .js-isInFrame .redirect-overlay, .js-showBackendV1 .redirect-overlay {
        display: none;
    }
    .redirect-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: white;
        text-align: center;
        padding-top: 50px;
        z-index: 999;
    }
</style>
<div class="redirect-overlay">
    <img src="/img/keelearning-connecting-knowledge.webp" style="margin-bottom: 60px;" />
    <h1 style="margin-bottom: 20px;">Wir sind umgezogen!</h1>
    Unseren neuen Administrationsbereich finden Sie ab sofort unter:<br>
    <a href="https://myadmin.keelearning.de"><strong style="font-size: 18px;line-height: 36px;color: #18b7ce;">myadmin.keelearning.de</strong></a><br>
    <p style="margin-top: 15px;">Sie werden in wenigen Sekunden umgeleitet.</p>
</div>
