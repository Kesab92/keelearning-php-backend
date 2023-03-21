/**
 * Functionality for the test list
 */
$(function() {

    $(".add-test").click(function() {
        $('.add-test-modal').modal('show');
    });

    // IE fix, because submit outside of the form itself is not triggered properly
    $('.button.approve.new-test-form').click(function(e) {
        e.preventDefault();
        $('#new-test-form').trigger('submit');
    });

    $(".test-results").click(function() {
        window.location.href = "/tests/" + $(this).data("test-id") + "/results";
        return false;
    })


    var $loadingScreen = $('.loading-screen');

    // Remove Test
    $(".remove-test").click(function () {
      var value = $(this).data('test-id');
      $('input[type="hidden"][name="test-id"]').val(value);

      var submissions = $(this).data('test-submissions');
      if (submissions > 0) {
        $loadingScreen.show();
        $.post('/tests/' + value + '/deleteInformation')
          .done(function(response) {
            if (response.success) {
              var data = response.data;

              $(".certificate-count").html(data.certificateTemplate);
              $(".submission-count").html(data.submissions);

              $loadingScreen.hide();
              $('.remove-test-modal').modal('show');
            }
          })
          .fail(function () {
            $loadingScreen.hide();
            alert("Es ist ein Fehler aufgetreten. Bitte probieren Sie es erneut.")
          });
      } else {
        $('button.delete-test').trigger('click');
      }
      return false
    })

    // Remove Test - Confirm
    $('button.delete-test').click(function() {
      $loadingScreen.show();
      var testId = $('input[type="hidden"][name="test-id"]').val();
      $.post('/tests/' + testId + '/remove', function(data) {
        if (data.success) {
          $('a[data-test-id="' + testId + '"]').remove()
          $loadingScreen.hide();
        }
      });
    });

    // Archive Test
    $('button.archive-test').click(function() {
      var $self = $(this)
      $loadingScreen.show();
      $.post('/tests/' + $self.data('test-id') + '/archive', function(data) {
        if (data.success) {
          $('a[data-test-id="' + $self.data('test-id') + '"]').remove()
          $loadingScreen.hide();
        }
      });
      return false
    });

    // Use filter
    $('select[name="filter"]').change(function() {
      $(this).closest('form').submit();
    })
});
