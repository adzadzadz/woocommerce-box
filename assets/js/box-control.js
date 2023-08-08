jQuery(function($) {
    console.log("Start Adz");
    update_box_status();

    // Ajax request to update box status
    function update_box_status() {
        console.log("Updating box status");
        $.ajax({
            url: wcb_box_control.ajax_url,
            type: 'post',
            data: {
                action: 'wcb_update_box'
            },
            success: function(response) {
                let data = JSON.parse(response);
                console.log(data);
                let cart_items_point_value = data.cart_items_point_value;
                let box_view = $('.mcs_wcb_box_view');
                let rate = (cart_items_point_value / data.boxes.max_point_value ) * 100;
                rate = rate.toFixed(2) + "%";
                box_view.find(".mcs_wcb_title").text(rate);
                box_view.find(".mcs_wcb_stats").html(`
                    <div class="mcs_wcb_stats_item">Boxes: ${data.boxes.size}</div>
                    <div class="mcs_wcb_stats_item">Cart Point Value: ${cart_items_point_value}</div>
                    <div class="mcs_wcb_stats_item">Max Point Value: ${data.boxes.max_point_value}</div>
                `)
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