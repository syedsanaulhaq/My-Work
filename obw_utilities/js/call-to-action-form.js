(function ($) {

    // Case action load
    Drupal.behaviors.ctaform = {
        attach: function (context, settings) {
            if ($('.webform-entity-select').length > 0 && $('.webform-entity-select')[0].selectedIndex > 0 ) {
                $('.webform-entity-select').HiddenCTALink();
            }
            $(document).once('#edit-field-story-call-to-actions-wrapper td div .form-submit').ajaxComplete(function (e, xhr, settings) {
              if (e.currentTarget.activeElement.name !== "field_story_call_to_actions[form][inline_entity_form][field_cta_link][0][uri]") {
                if ($('.webform-entity-select').length > 0 && $('.webform-entity-select')[0].selectedIndex > 0) {
                  $('.webform-entity-select').HiddenCTALink();
                  // ROLE WRITER
                  if ($("form.role-writer").length > 0) {
                    $('.webform-entity-select')
                        .find("optgroup,option")
                        .hide()
                        .filter("[value],[label='Sign up form'],[label='Sign up form'] > *")
                        .show();
                  }
                }
              }
            });
        }
    };

    // Case action change - on Story
    $('#node-story-edit-form').on('change', '.webform-entity-select', function() {
        $('.webform-entity-select').HiddenCTALink();
    });

    $('#node-story-form').on('change', '.webform-entity-select', function() {
        $('.webform-entity-select').HiddenCTALink();
    });

    // Case action change - on Organisations
    $('#node-organisations-edit-form').on('change', '.webform-entity-select', function() {
        $('.webform-entity-select').HiddenCTALink();
    });

    $('#node-organisations-form').on('change', '.webform-entity-select', function() {
        $('.webform-entity-select').HiddenCTALink();
    });

    // Case action change - on Call to action
    $('#node-call-to-action-edit-form').on('change', '.webform-entity-select', function() {
        $('.webform-entity-select').HiddenCTALink();
    });

    $('#node-call-to-action-form').on('change', '.webform-entity-select', function() {
        $('.webform-entity-select').HiddenCTALink();
    });


    $.fn.HiddenCTALink = function () {
        var value = $(this).val();
        if (value.length === 0) {
            $('.field--name-field-cta-link .fieldset-wrapper > div:first > input').val('');
            $('.field--name-field-cta-link .fieldset-wrapper > div:first').show();
        }
        else {
            $('.field--name-field-cta-link .fieldset-wrapper > div:first > input').val('http://example.com');
            $('.field--name-field-cta-link .fieldset-wrapper > div:first').hide();
        }
    };



})(jQuery);

