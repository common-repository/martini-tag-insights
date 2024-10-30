var linkForDeactivePluginMartiniTagInsights = jQuery("#the-list").find('[data-slug="martini-tag-insights"] span.deactivate a'),
    hrefLinkDeactevePluginMartiniTagInsights = linkForDeactivePluginMartiniTagInsights[0].href;

linkForDeactivePluginMartiniTagInsights.on('click', function (event) {
    event.preventDefault();
    jQuery('#martini-tag-insights-form').show();
});

jQuery('#martini_tag_insights_close').on('click', function (event) {
    event.preventDefault();
    jQuery('#martini-tag-insights-form').hide();
});

jQuery('#martini-tag-insight-add-feedback').on('click', function () {
    var radioButton = jQuery('#martini-tag-insights-form .martini_tag_insights_form_radio_btn input[name="martini-tag-insights-feeedback-radio-btn"][checked]')[0],
        martini_tag_insights_feedback = radioButton.value;

    if (radioButton.id === 'radio-5') {
        martini_tag_insights_feedback = jQuery('#martini-tag-insight-textarea')[0].value;
    }
    
    jQuery.ajax({
        type: 'GET',
        url: ajaxurl,
        dataType: 'json',
        data: {action: 'martini_tag_insights_add_feedback', text: martini_tag_insights_feedback},
        success: MartinTagInsightsFeedback,
    });
});

jQuery('#martini-tag-insight-skip').on('click', function () {
    window.location = hrefLinkDeactevePluginMartiniTagInsights;
});

jQuery('#martini-tag-insights-form .martini_tag_insights_form_radio_btn input[name="martini-tag-insights-feeedback-radio-btn"]').on('change', function () {
    if (this.id === 'radio-5') {
        jQuery('#martini-tag-insight-textarea').show();
    } else {
        jQuery('#martini-tag-insight-textarea').hide();
    }

    jQuery('#martini-tag-insights-form .martini_tag_insights_form_radio_btn' +
        ' input[name="martini-tag-insights-feeedback-radio-btn"][checked]').eq(0).attr( 'checked', false );
    jQuery(this).attr( 'checked', 'checked' );
});

function MartinTagInsightsFeedback (response) {
    if (response.data.error_text) {
        toastada.error(response.data.error_text);
        return;
    }

    if (response.success) {
        window.location = hrefLinkDeactevePluginMartiniTagInsights;
    }
}