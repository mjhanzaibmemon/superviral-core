<!DOCTYPE html>

<html lang="en">




<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>GA4</title>

    <!-- Google Tag Maasdasdnager -->

    <!-- Google tag (gtag.js) -->

    <script async src="https://www.googletagmanager.com/gtag/js?id=G-C18K306XYW"></script>

    <script>

    window.dataLayer = window.dataLayer || [];




    function gtag() {

        dataLayer.push(arguments);

    }

    gtag('js', new Date());




    gtag('config', 'G-Z2C75EWMXL');

    </script>

    <script>

    (function(w, d, s, l, i) {

        w[l] = w[l] || [];

        w[l].push({

            'gtm.start': new Date().getTime(),

            event: 'gtm.js'

        });

        var f = d.getElementsByTagName(s)[0],

            j = d.createElement(s),

            dl = l != 'dataLayer' ? '&l=' + l : '';

        j.async = true;

        j.src =

            'https://www.googletagmanager.com/gtm.js?id=' + i + dl;

        f.parentNode.insertBefore(j, f);

    })(window, document, 'script', 'dataLayer', 'GTM-NH3B6FF');

    </script>

    <!-- End Google Tag Manager -->




</head>




<body>

    <!-- Google Tag Manager (noscript) -->

    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NH3B6FF" height="0" width="0"

            style="display:none;visibility:hidden"></iframe></noscript>

    <!-- End Google Tag Manager (noscript) -->




    <div>This is where the purchase form would go</div>

    <!-- <button id="purchase">Purchase</button> -->

    <script>

    // document.getElementById("purchase").addEventListener("click", function () {

    dataLayer.push({

        ecommerce: null

    }); // Clear the previous ecommerce object.

    dataLayer.push({

        event: "purchase",

        ecommerce: {

            transaction_id: "T_12345",

            value: 25.42,

            tax: 4.90,

            shipping: 5.99,

            currency: "USD",

            coupon: "SUMMER_SALE",

            items: [{

                    item_id: "SKU_12345",

                    item_name: "Stan and Friends Tee",

                    affiliation: "Google Merchandise Store",

                    coupon: "SUMMER_FUN",

                    discount: 2.22,

                    index: 0,

                    item_brand: "Google",

                    item_category: "Apparel",

                    item_category2: "Adult",

                    item_category3: "Shirts",

                    item_category4: "Crew",

                    item_category5: "Short sleeve",

                    item_list_id: "related_products",

                    item_list_name: "Related Products",

                    item_variant: "green",

                    location_id: "ChIJIQBpAG2ahYAR_6128GcTUEo",

                    price: 9.99,

                    quantity: 1

                },

                {

                    item_id: "SKU_12346",

                    item_name: "Google Grey Women's Tee",

                    affiliation: "Google Merchandise Store",

                    coupon: "SUMMER_FUN",

                    discount: 3.33,

                    index: 1,

                    item_brand: "Google",

                    item_category: "Apparel",

                    item_category2: "Adult",

                    item_category3: "Shirts",

                    item_category4: "Crew",

                    item_category5: "Short sleeve",

                    item_list_id: "related_products",

                    item_list_name: "Related Products",

                    item_variant: "gray",

                    location_id: "ChIJIQBpAG2ahYAR_6128GcTUEo",

                    price: 20.99,

                    promotion_id: "P_12345",

                    promotion_name: "Summer Sale",

                    quantity: 1

                }

            ]

        }

    });




 

    // });

    </script>




</body>




</html>

