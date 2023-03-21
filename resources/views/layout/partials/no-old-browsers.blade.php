<style>
    #content, .login-wrapper, header {
        display: none;
    }
    .no-old-browsers {
        width: 460px;
        max-width: 90%;
        border: 2px solid black;
        background: white;
        padding: 20px 20px;
        margin: 100px auto 0 auto;
        display: block;
        border-radius: 5px;
        color: #3d3d53;
    }
    .no-old-browsers h1 {
        font-size: 1.75rem;
        font-weight: bold;
    }

    @supports ((--css: variables)) {
        #content, .login-wrapper {
            display: block;
        }
        header {
            display: flex;
        }
        .no-old-browsers {
            display: none;
        }
    }
</style>
<div class="no-old-browsers">
    <h1>Alte Browser werden von dieser Anwendung leider nicht unterstützt.</h1>
    <p>
        Bitte nutzen Sie die aktuellste Version einer dieser Browser:
    </p>
    <ul>
        <li>Google Chrome</li>
        <li>Microsoft Edge</li>
        <li>Mozilla Firefox</li>
        <li>Safari</li>
    </ul>
</div>
