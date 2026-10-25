document.addEventListener("DOMContentLoaded", function () {

    //
    var isFlutterInAppWebViewReady = false;
    window.addEventListener("flutterInAppWebViewPlatformReady", function (event) {
        isFlutterInAppWebViewReady = true;
    });
    //custom code
    livewire.on("openExternalBrowser", url => {
        if (isFlutterInAppWebViewReady) {
            window.flutter_inappwebview.callHandler('handlerOpenLink', url, true);
        }
    });


    //Stripe
    livewire.on("initStripe", (data) => {
        var stripe = Stripe(data[0]);
        stripe.redirectToCheckout({ sessionId: "" + data[1] + "" });
    });

    //Paystack
    livewire.on("initPaystack", (data) => {
        let handler = PaystackPop.setup({
            key: data[0], // Replace with your public key
            email: data[1],
            amount: data[2],
            currency: data[3],
            ref: "" + data[4] + "", // generates a pseudo-unique reference. Please replace with a reference you generated. Or remove the line entirely so our API will generate one for you
            // label: "Optional string that replaces customer email"
            onClose: function () {
                window.location.href = "" + data[5] + "";
            },
            callback: function (response) {
                window.location.href = "" + data[5] + "";
            },
        });
        handler.openIframe();
    });

    //Razorpay
    livewire.on("initRazorpay", (data) => {

        var options = {
            key: "" + data[0] + "", // Enter the Key ID generated from the Dashboard
            amount: "" + data[1] + "", // Amount is in currency subunits. Default currency is INR. Hence, 50000 refers to 50000 paise
            currency: "" + data[2] + "",
            name: "" + data[3] + "",
            description: "",
            image: "" + data[4] + "",
            order_id: "" + data[5] + "", //This is a sample Order ID. Pass the `id` obtained in the response of Step 1
            redirect: true,
            callback_url: data[6],
            handler: function (response) {
                window.location.href = "" + data[7] + "";
            },
            theme: {
                // color: "" + data[7] + ""
            },
        };
        var rzp1 = new Razorpay(options);
        rzp1.on("payment.failed", function (response) {
            alert(data[6]);
            window.location.href = "" + data[6] + "";
        });
        rzp1.open();
    });

    //Flutterwave
    livewire.on("initFlwPayment", (data) => {

        const flwPayModal = FlutterwaveCheckout({
            public_key: "" + data[0] + "",
            tx_ref: "" + data[1] + "",
            amount: "" + data[2] + "",
            currency: "" + data[3] + "",
            country: "" + data[4] + "",
            payment_options: "card,mobilemoney,ussd,banktransfer",
            // specified redirect URL
            redirect_url: "" + data[5] + "",
            meta: {
                // consumer_id: 23,
                // consumer_mac: "92a3-912ba-1192a"
            },
            customer: {
                email: "" + data[6][0] + "",
                phone_number: "" + data[6][1] + "",
                name: "" + data[6][2] + "",
            },
            callback: function (payment) {
                console.log("------------- START PAYMENT CALLBACK FLW -----------");
                console.log(payment);
                console.log("------------- END -----------");
                flwPayModal.close();
            },
            onclose: function (incomplete) {
                if (incomplete === true) {
                    console.log("Flutterwave pay modal  close");
                }
            },
            customizations: {
                title: "" + data[7][0] + "",
                description: "",
                logo: "" + data[7][1] + "",
            },
        });
    });

    //paypal
    livewire.on("initPaypalPayment", (mainData) => {
        //
        paypal
            .Buttons({
                createOrder: function (data, actions) {
                    // This function sets up the details of the transaction, including the amount and line item details.
                    return actions.order.create({
                        purchase_units: [
                            {
                                amount: {
                                    value: mainData[0],
                                    currency: mainData[1],
                                },
                            },
                        ],
                    });
                },
                onApprove: function (data, actions) {
                    // This function captures the funds from the transaction.
                    return actions.order.capture().then(function (details) {
                        window.location.href =
                            "" +
                            mainData[2] +
                            "&transaction_id=" +
                            details["id"] +
                            "";
                    });
                },
            })
            .render("#paypal-button-container");
    });

    //paytm
    livewire.on("initPayTmPayment", (mainData) => {
        //
        let paymentData = mainData;
        const setVal = (id, val) => {
            const el = document.getElementById(id);
            if (el) el.value = val;
        };

        setVal("paytm_REQUEST_TYPE", paymentData["REQUEST_TYPE"]);
        setVal("paytm_MID", paymentData["MID"]);
        setVal("paytm_ORDER_ID", paymentData["ORDER_ID"]);
        setVal("paytm_CUST_ID", paymentData["CUST_ID"]);
        setVal("paytm_INDUSTRY_TYPE_ID", paymentData["INDUSTRY_TYPE_ID"]);
        setVal("paytm_CHANNEL_ID", paymentData["CHANNEL_ID"]);
        setVal("paytm_TXN_AMOUNT", paymentData["TXN_AMOUNT"]);
        setVal("paytm_WEBSITE", paymentData["WEBSITE"]);
        setVal("paytm_CALLBACK_URL", paymentData["CALLBACK_URL"]);
        setVal("paytm_MOBILE_NO", paymentData["MOBILE_NO"]);
        setVal("paytm_EMAIL", paymentData["EMAIL"]);
        setVal("paytm_CHECKSUMHASH", paymentData["CHECKSUMHASH"]);

        //
        if (document.payTmForm) document.payTmForm.submit();
    });
    //payu
    livewire.on("initPayUPayment", (paymentData) => {
        //
        const setVal = (id, val) => {
            const el = document.getElementById(id);
            if (el) el.value = val;
        };

        setVal("payU_key", paymentData["payU_key"]);
        setVal("payU_hash_string", paymentData["payU_hash_string"]);
        setVal("payU_hash", paymentData["payU_hash"]);
        setVal("payU_txnid", paymentData["payU_txnid"]);
        setVal("payU_amount", paymentData["payU_amount"]);
        setVal("payU_firstname", paymentData["payU_firstname"]);
        setVal("payU_email", paymentData["payU_email"]);
        setVal("payU_phone", paymentData["payU_phone"]);
        setVal("payU_productinfo", paymentData["payU_productinfo"]);
        setVal("payU_surl", paymentData["payU_surl"]);
        setVal("payU_furl", paymentData["payU_furl"]);
        setVal("payU_service_provider", paymentData["payU_service_provider"]);

        //
        if (document.payuform) document.payuform.submit();
    });

    //custom payment
});

