/**
 * Functionality for the competition list and competition editing
 */
$(function() {

    // Calendar options
    var calendarOptions = {
      type:'date',
      firstDayOfWeek: 1,
      text: {
        days: ['S', 'M', 'D', 'M', 'D', 'F', 'S'],
        months: ['Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'],
        monthsShort: ['Jan', 'Feb', 'Mär', 'Apr', 'Mai', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dez'],
        today: 'Heute',
        now: 'Jetzt',
        am: 'AM',
        pm: 'PM'
      },
      formatter: {
        date: function(date, settings) {
          return date.getDate() + '.' + (date.getMonth()+1) + '.' + date.getFullYear()
        },
      },
      parser: {
        date: function (text, settings) {
          var parts = text.match(/(\d+)/g)
          if (!parts || parts.length < 3) {
            return null
          }
          return new Date(parts[2], parts[1]-1, parts[0])
        },
      },
    }

    // Check if we want to edit a specific competition
    var editCompetitionId = getQueryParameter("edit")
    if (editCompetitionId) {
        showCompetition(editCompetitionId)
    }

    var tagField = $('#tag-select')

    $('#tag-select').dropdown()

    function validateCreationForm() {
      var validTags = $('#tag-select').val() && ($('#tag-select').val().length > 1 || !!$('#tag-select').val()[0])
      return !!(
        validTags &&
        $('input[name="title"]').val() &&
        $('#category-select').val() &&
        $('input[name="start_at"]').val() &&
        $('input[name="duration"]').val() &&
        $('textarea[name="description"]').val()
      )
    }

    $(".add-competition").click(function() {
        $('.add-competition-modal').modal({
            onApprove: function() {
                return validateCreationForm()
            }
        })
            .modal('show')

        $('#startCompetition').calendar(calendarOptions)
    })

    tagField.on('invalid', function(e) {
        e.preventDefault()
        $(this)
            .parent()
            .siblings('label')
            .css('color', 'red')
    })
    tagField.closest('.ui.dropdown').click(function() {
        enableFormField('tags')
    })

    $('#category-select').on('invalid', function(e) {
        e.preventDefault()
        $(this)
            .parent()
            .siblings('label')
            .css('color', 'red')
    })

    $('input[name=duration]').on('invalid', function(e) {
        $(this)
            .siblings('label')
            .css('color', 'red')
    })

    $(".competition").click(function() {
        $(".competitions-wrapper .visible").removeClass("visible")
        $(this).addClass("visible")
        showCompetition($(this).data("competition-id"))
    })

    var vueInstance = null

    // IE fix, because submit outside of the form itself is not triggered properly
    $('.button.approve.new-competition-form').click(function(e) {
        e.preventDefault()
        if (validateCreationForm()) {
          $('input[type=submit].ie-submit-fix.new-competition-form').trigger('click')
        }
    })

    function setSubmitButtonState() {
      $('.button.approve.new-competition-form').prop('disabled', !validateCreationForm())
    }
    $('input, textarea, select', $('#new-competition-form')).change(setSubmitButtonState).keyup(setSubmitButtonState)

    function showCompetition(competitionId, tagIds) {
        $(".competition-content").addClass("loading")
        showCompetitionContainer()

        if (vueInstance) {
            vueInstance.$destroy()
        }
        var queryParams = {}
        if(tagIds) {
          queryParams.tagIds = tagIds
        }
        queryParams = new URLSearchParams(queryParams).toString()

        $(".competition-content").load("/competitions/" + competitionId, queryParams, function() {

            // Disable the loading state
            $(".competition-content").removeClass("loading")

            // Update
            $('.update-user').click(function () {
                $(".competition-content").addClass("loading")
                $.post('/competitions/' + $(this).data('competition-id') + '/update', {
                    start_at: $('.start-at').val(),
                    description: $('.description').html()
                }, function (data) {
                    if (data.success) {
                        $(".competition-content").removeClass("loading")
                    }
                })
            })

            // Initialize Editor
            new MediumEditor('.full-editable', {
                toolbar: true
            })

            vueInstance = new window.Vue({
                el: '.image-cropper',
            })

            // Calendar
            if($('#setCompetition').length) {
              $('#setCompetition').calendar(calendarOptions)
            }

            $('.tag-search').dropdown().change(function() {
              showCompetition(competitionId, $(this).find("select").val())
            })

            // Delete cover image
            $(".delete-cover-image").click(function(e) {
                if(confirm('Möchten Sie das Cover-Image wirklich entfernen?')) {
                    deleteCoverImage(competitionId)
                } else {
                    return false
                }
            })

            $(".cover-upload-btn").click(function() {
                showCompetitionUploadModal()
            })
        })
    }

    function showCompetitionContainer() {
        $(".competitions-wrapper").addClass("editing")
    }

    function enableFormField(name) {
        if(name == 'tags') {
            tagField.attr('required', 'required')
            tagField.closest('.field').addClass('required')
        }
        $('label').css('color', '#000')
    }

    function deleteCoverImage(competitionId) {
        $.get("/competitions/" + competitionId + "/removecover", function() {
          location.href = location.origin + '/competitions'
        })
    }

    function showCompetitionUploadModal() {
        $('.ui.modal.image-cropper').modal().modal('show')
    }
})
