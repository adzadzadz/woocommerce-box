jQuery(function ($) {
    console.log("MCS BOX Started");
    update_box_status();

    // Ajax request to update box status
    function update_box_status() {
        console.log("Updating box status");
        
        wcb_box_targeted_display($(".mcs_wcb_box_view"));

        $.ajax({
            url: wcb_box_control.ajax_url,
            type: 'post',
            data: {
                action: 'wcb_update_box'
            },
            success: function (response) {
                let data = JSON.parse(response);
                console.log(data);
                let box_view = $('.mcs_wcb_box_view');
                box_view.find(".mcs_wcb_progress_bar").css("width", data.progress + "%");
                box_view.find(".mcs_wcb_progress_bar .progress_value").text(data.progress + "%");

                let info_text = '';
                let info_post_text = '';
                let box_count = {
                    'small': 0,
                    'large': 0
                };
                data['boxes']['size'].forEach(function (size, idx) {
                    if (size == 'Small') {
                        box_count['small'] += 1;
                    }
                    else if (size == 'Large') {
                        box_count['large'] += 1;
                    }

                    if (idx == 0 && data['boxes']['size'].length == 1) { // catch if only 1 box
                        info_post_text += `is`
                        return;
                    }
                    else if (idx == data['boxes']['size'].length - 1) {
                        info_post_text += `are`
                    }
                });

                if (box_count.small > 0)
                    info_text += `<strong>${box_count.small}</strong> Small box`;

                if (box_count.small > 0 && box_count.large > 0)
                    info_text += ` and `;

                if (box_count.large > 0)
                    info_text += `<strong>${box_count.large}</strong> Large box`;

                info_text += ` ${info_post_text} ${data.progress}% full.`;
                console.log("info text:", info_text);
                box_view.find(".mcs_wcb_info").html(info_text);

            }
        });
    }


    $(document.body).on('added_to_cart', function (event, fragments, cart_hash, $button) {
        console.log('Added to cart');
        // Your code to run when an item is added to the cart
        update_box_status();
    });

    $(document.body).on('updated_cart_totals', function () {
        // Your code to run when the cart is updated
        console.log('Cart has been updated!');
        update_box_status();
    });

    $(document.body).on('removed_from_cart', function () {
        // Your code to run when the cart is updated
        console.log('Cart has been updated!');
        update_box_status();
    });

    $(document.body).on('updated_wc_div', function () {
        // Your code to run when the cart is updated
        console.log('Cart has been updated!');
        update_box_status();
    });

    function wcb_box_targeted_display(box_view) {
        $(".widget_shopping_cart_content").ready(function () {

            $(this).find(".mcs_wcb_box_view").remove();

            let clone = $(box_view).clone(true, true);
            console.log("Box View Appending");
            let cart_widget_elem = $(".widget_shopping_cart_content");
            console.log(cart_widget_elem);
            cart_widget_elem.prepend(clone.css("display", "block"));
            console.log("box view:");
            console.log(clone);
        });
    }

});