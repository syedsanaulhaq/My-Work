(function ($, Drupal, drupalSettings) {

  $('.js-obw-validate-form-empty input[name="i_have_read_and_agree_to_the_collection_use_and_disclosure_of_pe"]').on('click', function (e) {
    setButtonSubmit();
  });

  $(".js-obw-validate-form-empty #edit-pick-one-of-our-values").on('change', function (e) {
    setButtonSubmit();
  });

  $(".js-obw-validate-form-empty #edit-share-your-story").on('keydown keyup change', function (e) {
    setButtonSubmit();
    check100Words($(this), e);
  });

  $(".js-obw-validate-form-empty #edit-first-name").on('keydown keyup change', function (e) {
    setButtonSubmit();
  });

  $(".js-obw-validate-form-empty #edit-last-name").on('keydown keyup change', function (e) {
    setButtonSubmit();
  });

  $(".js-obw-validate-form-empty #edit-email").on('keydown keyup change', function (e) {
    console.log('asdasd');
    setButtonSubmit();
  });

  $(".js-obw-validate-form-empty #edit-nationality").on('change', function () {
    setButtonSubmit();
  });

  $(".js-obw-validate-form-empty #edit-country").on('change', function () {
    setButtonSubmit();
  });

  $(".js-obw-validate-form-empty #edit-occupation").keyup(function () {
    setButtonSubmit();
  });

  function setButtonSubmit() {
    $button = $('.js-obw-validate-form-empty #edit-actions-submit');
    console.log('asdasdsa');
    $button.attr("disabled", true);
    if (recheckFields() === true) {
      $button.removeAttr('disabled');
    }
  }

  function check100Words($selector, e) {
    if ($selector.val().split(" ").length > 100) {
      var error_message = $('<div id="item-share-your-story-error-message" class="ui basic red pointing prompt label transition visible" style="display: block !important;">Please keep your story within 100 words.</div>');
      if ($('#item-share-your-story-error-message').length === 0) {
        $selector.closest('div.form-item-share-your-story').append(error_message);
      }
      $selector.closest('div.form-item-share-your-story').addClass('error-custom');
      if (e.keyCode !== 46 && e.keyCode !== 8) {
        e.preventDefault();
      }
    } else {
      $selector.closest('div.form-item-share-your-story').removeClass('error-custom');
      $('#item-share-your-story-error-message').remove();
    }
  }

  function recheckFields() {
    if ($('.js-obw-validate-form-empty #edit-pick-one-of-our-values').val() === "" ||
      $('.js-obw-validate-form-empty #edit-share-your-story').val() === "" ||
      $('.js-obw-validate-form-empty #edit-first-name').val() === "" ||
      $('.js-obw-validate-form-empty #edit-last-name').val() === "" ||
      $('.js-obw-validate-form-empty #edit-email').val() === "" ||
      $('.js-obw-validate-form-empty #edit-nationality').val() === "" ||
      $('.js-obw-validate-form-empty #edit-country').val() === "" ||
      $('.js-obw-validate-form-empty #edit-occupation').val() === "" ||
      $('.js-obw-validate-form-empty input[name="i_have_read_and_agree_to_the_collection_use_and_disclosure_of_pe"]:checked').length === 0) {
      return false;
    }
    return true;
  }

})(jQuery, Drupal, drupalSettings);
