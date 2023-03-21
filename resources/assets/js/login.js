$(document)
        .ready(function () {
            $("#app-select").dropdown();
            
            function disableLogin() {
                $(".password-field, .remember-field, .submit.button").addClass('disabled')
                $(".app-select").hide()
                $(".loginform").addClass('emailstep');
                $(".loginform").removeClass('appstep');
            }

            function enableLogin() {
                $(".password-field, .remember-field, .submit.button").removeClass('disabled')
            }
            
            function searchApps() {
                disableLogin();
                if(!$('.emailinput').val()) {
                    return;
                }
                $.get('/login/apps', {
                    email: $('.emailinput').val() 
                }).done(function(apps) {
                    if(Array.isArray(apps) && apps.length === 0) {
                        disableLogin();
                        alert('Diese E-Mail Adresse wurde nicht gefunden.');
                    } else {
                        var $el = $("#app-select");
                        $el.empty(); // remove old options
                        $el.append($("<option></option>").attr("value","").text('App wählen'));
                        $.each(apps, function (key, value) {
                            $el.append($("<option></option>")
                                    .attr("value", key).text(value));
                        });
                        var activeKey = lastAppLogin
                        if(lastAppLogin === false) {
                            activeKey = Object.keys(apps)[0]
                        }
                        $el.val(activeKey)
                        
                        $(".loginform").removeClass('emailstep');
                        if(Object.keys(apps).length > 1) {
                            $("#app-select").dropdown('refresh')
                            $("#app-select").dropdown('set selected', activeKey)
                            $(".app-select").show()
                            $(".loginform").addClass('appstep');
                        }
                        enableLogin();
                    }
                }).fail(function() {
                    disableLogin();
                    alert('Es ist ein Fehler aufgetreten. Bitte wenden Sie sich an den Support.')
                })
                
            }
            
            window.setTimeout(searchApps, 100);
            
            $('.emailinput').change(function () {
                searchApps()
            });

            $('.ui.form')
                    .form({
                        fields: {
                            email: {
                                identifier: 'username',
                                rules: [
                                    {
                                        type: 'empty',
                                        prompt: 'Bitte gib Deinen Benutzernamen ein!'
                                    }
                                ]
                            },
                            password: {
                                identifier: 'password',
                                rules: [
                                    {
                                        type: 'empty',
                                        prompt: 'Bitte gib Dein Passwort ein!'
                                    },
                                    {
                                        type: 'length[4]',
                                        prompt: 'Das Passwort muss länger als 4 Zeichen sein!'
                                    }
                                ]
                            }
                        }
                    });
        });