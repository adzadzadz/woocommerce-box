jQuery(function($) {
    console.log("Start Adz");
    update_box_status();

    // Ajax request to update box status
    function update_box_status() {
        console.log(wcb_box_control.ajax_url);

        $.ajax({
            url: wcb_box_control.ajax_url,
            type: 'post',
            data: {
                action: 'wcb_update_box'
            },
            success: function(response) {
                console.log(response);
                let data = JSON.parse(response);
                let box_view = $('.mcs_wcb_box_view');
                box_view.find('.mcs_wcb_current_point_value').html(data.rate + '%');

                box_view.find('.mcs_wcb_stats').html('');
                for(let key in data) {
                    console.log(key);
                    console.log(data[key]);
                    box_view.find('.mcs_wcb_stats').append(`<div>${key}: ${data[key]}</div>`);
                }
                
            }
        });
    }

    $(document.body).on('added_to_cart', function(event, fragments, cart_hash, $button) {
        console.log('Added to cart');
        // Your code to run when an item is added to the cart
        update_box_status();
    });

    $(document.body).on('updated_cart_totals', function() {
        // Your code to run when the cart is updated
        console.log('Cart has been updated!');
        update_box_status();
    });

});