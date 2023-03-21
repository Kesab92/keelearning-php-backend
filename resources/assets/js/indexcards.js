/**
 * Functionality for the indexcard list and indexcard editing
 */
$(function() {
    $(".indexcard").click(function() {
        $(".indexcards-wrapper .active").removeClass("active")
        $(this).addClass("active")
        $('.imagemap-modal').remove()
        showindexcard($(this).data("indexcard-id"))
    })

    $(".add-indexcard").click(function () {
        $('.add-indexcard-modal').modal('show')
    })

    // IE fix, because submit outside of the form itself is not triggered properly
    $('.button.approve.new-indexcard-form').click(function (e) {
        e.preventDefault()
        $('input[type=submit].ie-submit-fix.new-indexcard-form').trigger('click')
        return false
    })

    $(".tag-search").change(function() {
        $(this).closest("form").submit()
    })

  var labelTemplate = "<div class=\"imagemap-label\">\n" +
    "            <i class=\"handle blue bordered inverted bars icon\"></i>\n" +
    "            <input class=\"imagemap-label-input\" type=\"text\" value=\"%VALUE%\">\n" +
    "            <i class=\"imagemap-label-remove red circular inverted close icon\"></i>\n" +
    "        </div>"

    // Check if we want to edit a specific indexcard
    var editIndexcardId = getQueryParameter("edit")
    if (editIndexcardId) {
        showindexcard(editIndexcardId)
    }

    function showindexcard(indexcardId) {
        $(".indexcard-content").addClass("loading")
        showIndexcardContainer()

        $(".indexcard-content").load("/indexcards/" + indexcardId, function() {

            // Disable the loading state
            $(".indexcard-content").removeClass("loading")

            $('.ui.checkbox').checkbox()
            $('.has-popup').popup()
            $('.dropdown').dropdown()

            // Initialize the editor
            new MediumEditor('.editable', {
                toolbar: false
            })

            // Initialize the dropzone
            var myDropzone = new Dropzone("#indexcard-dropzone form", {
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                accept: function (file, done) {
                    if (file.size / 1024 / 1024 > 10) {
                        // Invalid size
                        myDropzone.removeFile(file)
                        alert("Die maximale Dateigröße ist 10 MB.")
                        done()
                    } else if (!(file.type.indexOf("image/") != -1)) {
                        // Invalid file type
                        myDropzone.removeFile(file)
                        alert("Es können nur Bilder hochgeladen werden!")
                        done()
                    } else {
                        // All good
                        done()
                    }
                },
                dictDefaultMessage: "<br/>Klicken Sie, oder ziehen Sie hier ein Bild hinein (Max. Dateigröße 10 MB).",
                clickable: true,
                maxFiles: 1,
                uploadMultiple: false,
                addRemoveLinks: true
            })
            myDropzone.on("complete", function (file) {
                location.href = location.origin + '/indexcards?edit=' + indexcardId
            })

            // Setup the delete event
            $(".indexcard-content").find(".delete-indexcard").click(function (e) {
                if (!confirm('Sind Sie sicher, dass Sie diese Karte löschen möchten?')) {
                    return false
                }
            })

            // Setup the save event
            $(".indexcard-content").find(".save-button").click(function () {
                var front = $(".indexcard-front").html()
                var back = $(".indexcard-back").html()
                var json = $(".indexcard-json").val()
                var category = $('select[name="category"]').val()

                $(".indexcard-content").addClass("loading")
                $.post("/indexcards/" + $(this).data("indexcard-id"), {
                    front: front,
                    back: back,
                    json: json,
                    category: category,
                }, function () {
                    $(".indexcard-content").removeClass("loading")
                })
            })

            // imagemap handling
            if(!$('.indexcard-image-preview').length) {
                $('.imagemap-button').addClass('disabled')
            } else {
                $('.imagemap-modal').modal({
                    closable: false,
                })

                $('.imagemap-button').click(function() {
                    $('.imagemap-modal').modal('show')
                    $('.imagemap-label').remove()

                    var labels = [],
                        containerWidth = $('.imagemap-wrapper').width(),
                        containerHeight = $('.imagemap-wrapper').height()
                    if($('.indexcard-json').val() && JSON.parse($('.indexcard-json').val())) {
                        labels = JSON.parse($('.indexcard-json').val())
                    }

                    for(var i = 0; i < labels.length; i++) {
                        var newLabel = $(labelTemplate.replace('%VALUE%', labels[i].text)),
                            top = containerHeight * labels[i].top,
                            left = containerWidth * labels[i].left
                        newLabel.attr('data-y', top)
                        newLabel.attr('data-x', left)

                        newLabel.css('top', (top / containerHeight * 100) + '%')
                        newLabel.css('left', (left / containerWidth * 100) + '%')
                        newLabel.appendTo('.imagemap-labels')
                        $('.imagemap-label-remove', newLabel).click(function() {
                            $(this).parent().remove()
                        })
                    }
                    setupImagemapDraggers()
                })
                $('.imagemap-button-save').click(function() {
                    var labels = [],
                        containerWidth = $('.imagemap-wrapper').width(),
                        containerHeight = $('.imagemap-wrapper').height()
                    $('.imagemap-label').each(function() {
                        var label = {}
                        label.text = $('input',this).val()
                        label.left = ($(this).attr('data-x') / containerWidth).toFixed(4)
                        label.top = ($(this).attr('data-y') / containerHeight).toFixed(4)
                        labels.push(label)
                    })
                    $('.indexcard-json').val(JSON.stringify(labels))
                    $('.imagemap-modal').modal('hide')
                })
                $('.imagemap-button-addlabel').click(function() {
                    var newLabel = $(labelTemplate.replace('%VALUE%',''))
                    newLabel.appendTo('.imagemap-labels')
                    $('.imagemap-label-remove', newLabel).click(function() {
                        $(this).parent().remove()
                    })
                    setupImagemapDraggers()
                })
            }
        })
    }

    function showIndexcardContainer() {
        $(".indexcards-wrapper").addClass("editing")
    }

    function setupImagemapDraggers() {
        interact('.imagemap-label')
          .allowFrom('.handle')
          .draggable({
            inertia: false,
            restrict: {
              restriction: "parent",
              endOnly: false,
              elementRect: { top: 0, left: 0, bottom: 1, right: 1 }
            },
            autoScroll: {
                enabled: false, //bugging out when used with modals
                container: jQuery('.modals').get(0)
            },
            onmove: dragMoveListener,
            onstart: function (event) {
              jQuery(event.target).addClass('dragging')
            },
            onend: function (event) {
              jQuery(event.target).removeClass('dragging')
            }
          })
    }

    function dragMoveListener (event) {
        var target = $(event.target),
            x = (parseFloat(target.attr('data-x')) || 0) + event.dx,
            y = (parseFloat(target.attr('data-y')) || 0) + event.dy,
            containerWidth = $('.imagemap-wrapper').width(),
            containerHeight = $('.imagemap-wrapper').height()

        target.attr('data-x', x)
        target.attr('data-y', y)

        target.css('top', (y / containerHeight * 100) + '%')
        target.css('left', (x / containerWidth * 100) + '%')
      }

      $(window).resize(function() {
          var containerWidth = $('.imagemap-wrapper').width(),
          containerHeight = $('.imagemap-wrapper').height()
          $('.imagemap-label').each(function() {
              var label = $(this)
              label.attr('data-x', containerWidth * label[0].style.left.slice(0, -1) / 100)
              label.attr('data-y', containerHeight * label[0].style.top.slice(0, -1) / 100)
          })
      })
})
