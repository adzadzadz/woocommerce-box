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
                let box_view = $('.mcs_wcb_box_view');
                box_view.find(".mcs_wcb_progress_bar").css("width", data.progress + "%");
                box_view.find(".mcs_wcb_progress_bar .progress_value").text(data.progress + "%");

                let info_text = '';
                data['boxes']['size'].forEach(function(size, idx) {
                    if (idx == 0 && data['boxes']['size'].length == 1) { // catch if only 1 box
                        info_text += `<strong>1</strong> ${size} box is`
                        return;
                    }

                    if (idx == 0) { // catch first loop
                        info_text += `<strong>1</strong> ${size} box`;
                    } 
                    else if (idx == data['boxes']['size'].length - 1) { // catch last loop
                        info_text += ` and <strong>1</strong> ${size} box are`;   
                    }
                    else { // catch if not first or last loop
                        info_text += `, <strong>1</strong> ${size} box`;
                    }
                    console.log("size", size);
                });

                info_text += ` ${data.progress}% full.`;

                box_view.find(".mcs_wcb_info").html(info_text);

                // box_view.find(".mcs_wcb_stats").html(`
                //     <div class="mcs_wcb_stats_item">Boxes: ${data.boxes.size}</div>
                //     <div class="mcs_wcb_stats_item">Cart Point Value: ${cart_items_point_value}</div>
                //     <div class="mcs_wcb_stats_item">Max Point Value: ${data.boxes.max_point_value}</div>
                // `)
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