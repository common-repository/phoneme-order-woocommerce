(function($) {
  $(document).ready(function() {

    $('#phoneme-order-woocommerce-form')
      .on('phoneMeOrderWC-checkOrdered', function(event, product_or_variant_id) {
        var ids = window.sessionStorage.getItem('PhoneMeOrderWC');
        if (ids) {
          ids = ids.split(',');
          if ($.inArray(product_or_variant_id, ids) >= 0) {
            $(this).hide();
          }
          else {
            $(this).show();
          }
        }
      })
      .on('phoneMeOrderWC-placeOrder', function(event, product_or_variant_id) {
        var ids = window.sessionStorage.getItem('PhoneMeOrderWC');
        if (ids) {
          ids = ids.split(',');
        }
        else {
          ids = [];
        }
        ids.push(product_or_variant_id);
        ids = jQuery.unique(ids);
        window.sessionStorage.setItem('PhoneMeOrderWC', ids.join(','));
      })
      .trigger('phoneMeOrderWC-checkOrdered', $(':input[name="product_id"]', this).val());


    $('.outofstock .phoneme-order-woocommerce')
      .addClass('phoneme-order-woocommerce-disabled')
      .find(':input').attr('disabled', 'disabled').end();

    $(':submit', '#phoneme-order-woocommerce-form')
      .on('click', function (event) {
        var $form = $('#phoneme-order-woocommerce-form');
        event.preventDefault();

        if ($form.closest('.product').hasClass('outofstock')) {
          return;
        }

        if ($('form.variations_form').length) {
          $($('form.variations_form').serializeArray()).each(function (key, val) {
            if (val.name == 'variation_id') {
              $('input[name="variation_id"]', $form).val(val.value);
            }
          });
        }

        var formSerializeStr = $(':input', $form).serialize();
        formSerializeStr += '&quantity=' + ($('.cart [name="quantity"]').val() ? $('.cart [name="quantity"]').val() : 1);

        var $formElements = $('.form-elements', $form);
        $formElements.addClass('ajax-processing');
        $.post($form.attr('data-ajax-url'), formSerializeStr, function(response) {
          $formElements.removeClass('ajax-processing');
          if (response.status) {
            $('#phoneme-order-woocommerce-form').trigger('phoneMeOrderWC-placeOrder', response.id);
            $formElements.hide();
          }
          $('.status-message', $form)
            .text(response.message);
        });

      });


  });

  $('.variations_form')
    .on('wc_variation_form', function() {
      $(this)
        .on('found_variation', function (event, variation) {
          $('.phoneme-order-woocommerce').toggleClass('phoneme-order-woocommerce-disabled', !variation.is_in_stock)
          $('#phoneme-order-woocommerce-form').trigger('phoneMeOrderWC-checkOrdered', variation.id);
        });
    });

}(jQuery));