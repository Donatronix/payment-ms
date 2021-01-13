<html lang="en">
<head>
    <title>Payment service. Full test.</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <style type="text/css">
        pre {
            width: 800px;
        }
        #json_decode  li
        {
            list-style-type: none;
        }

        #json_decode > ul > li
        {
            font-size:13pt;
        }

        #json_decode > ul > li > ul > li > ul > li
        {
            font-size:11pt;
        }

        #json_decode li > ul
        {
            border-left:1px solid gray;
            margin-left:20px;
            padding-left:10px;
        }

        @keyframes bg {
            from {background-color: #fafafa}
            to {background-color: #000000}
        }

        @keyframes wdth {
            from {width: 0}
            to {width: 20px}
        }

        #btn {
            display: none;
        }

        #dots {
            animation-name: wdth;
            animation-duration: 1s;
            animation-iteration-count: infinite;
            padding: 3px;
            overflow: hidden;
        }

        #stripebtns {
            display: none;
        }

        #coinbasebtns {
            display: none;
        }

    </style>
    <script type="text/javascript">

        function renderJson(jsonObject)
        {
            let html = '<ul>';
            for(var objType in jsonObject){
                if(typeof jsonObject[objType] === 'object')
                {
                    html += '<li>' + objType + ':';
                    html +=  renderJson(jsonObject[objType]) + '</li>';
                }
                else{
                    html += '<li>' + objType + ': ' + jsonObject[objType] + '</li>';
                }
            }
            html += '</ul>';
            return html;
        }

        let payment_id = 0;
        let interval_id = 0;
        let lastPayment = "";
        let check_code = "";
        let document_id = "";

        function startInterval()
        {
            if(!interval_id) {
                interval_id = window.setInterval(renewPayment, 1000);
            }
        }

        function doRenewPayment(data)
        {
            let str = JSON.stringify(data);
            if(str != lastPayment) {
                check_code = data.check_code;
                document_id = data.document_id;
                lastPayment = str;
                renewList(data);
            }
        }

        function renewList(data) {
            console.log(data);
            $("#renew_list").append("<br /><br /><pre>"+JSON.stringify(data)+"</pre><hr />"+renderJson(data));
            window.scrollTo(0, document.body.scrollHeight);
        }

        function renewPayment()
        {
            $.ajax({
                url: "/v1/payments/"+payment_id,
                method: "GET",
                cache: false,
                data: {},
                headers: {
                    "user-id": 2
                },
                success: doRenewPayment,
//                complete: function(data){console.log(data)},
                error: function(data){console.log("Error:");console.log(data);alert(data.responseText)},
                dataType: "json"
            });
        }

        function doStopRenew()
        {
            if(interval_id) {
                window.clearInterval(interval_id);
                interval_id = 0;
                $("#renew_list").append("<p><b>renew stopped</b></p>");
                window.scrollTo(0, document.body.scrollHeight);
                $("#renewBtn").prop("disabled", true);
                $("#btn").hide();
                $("#stripebtns").hide();
                $("#coinbasebtns").hide();
            }
        }

        function doStripe(key,sessid) {
            window.open("full-stripe?pubkey="+encodeURIComponent(key)+"&sessid="+encodeURIComponent(sessid), "_blank");
        }

        function doAnswer(data) {
            console.log(data);
            if(data.status=="error") {
                console.log("Error:");
                console.log(data);
                alert(data.message);
                return;
            }
            payment_id = data.payment_id;
            $("#renewBtn").prop("disabled", false);
            $("#btn").show();
            startInterval();
            if(data.gateway=="stripe") {
                doStripe(data.stripe_pubkey, data.session_id);
                $("#stripebtns").show();
            } else {
                if(data.gateway=="coinbase") {
                    $("#coinbasebtns").show();
                }
                window.open(data.invoice_url, "_blank");
            }
        }

        function doSubmit() {
            $.ajax({
                url: "/v1/payments/recharge",
                method: "POST",
                data: {
                    amount: $("#amount").val(),
                    currency: $("#currency").val(),
                    gateway: $("#gateway").val(),
                    service: $("#service").val()
                },
                headers: {
                    "user-id": 2
                },
                beforeSend: function() {$('#a').html(this.url)},
                success: doAnswer,
                complete: function(data){console.log(data);$("#response").html(data.responseText);$("#json_decode").html(renderJson(data.responseJSON));},
                error: function(data){alert(data.responseText)},
                dataType: "json"
            });
        }

        function doCoinbaseSuccess() {
            $.ajax({
                url: "/v1/payments/webhooks/coinbase/invoices",
                method: "POST",
                data: JSON.stringify({
                    event: {
                        data: {
                            id: document_id,
                            metadata: {
                                code: check_code,
                                payment_id: payment_id
                            }
                        },
                        type: "charge:confirmed"
                    }
                }),
                contentType: 'application/json',
                headers: {
                    "user-id": 2,
                }
            });
        }

        function doStripeRequest(type, payment_status) {
            $.ajax({
                url: "/v1/payments/webhooks/stripe/invoices",
                method: "POST",
                data: JSON.stringify({
                    type: type,
                    data: {
                        object: {
                            id: "cs_00000000000000",
                            metadata: {
                                "payment_order": payment_id,
                                "check_code": check_code
                            },
                            payment_intent: "pi_00000000000000",
                            payment_status: payment_status
                        }
                    }
                }),
                contentType: 'application/json',
                headers: {
                    "user-id": 2,
                }
            });
        }

        function doStripeSuccess() {
            doStripeRequest("checkout.session.async_payment_succeeded", "paid");
        }

        function doStripeFail() {
            doStripeRequest("checkout.session.async_payment_failed", "unpaid");
        }

    </script>
</head>
<body>

<h1>Payment service. Full test.</h1>

<table>
    <tr><td>amount:</td><td><input type="text" name="amount" value="10" id="amount"></td></tr>
    <tr><td>currency:</td><td><select name="currency" id="currency"><option value="USD">USD</option><option value="EUR">EUR</option></select></td></tr>
    <tr><td>gateway:</td><td><select name="gateway" id="gateway"><option value="paypal">PayPal</option><option value="coinbase">Coinbase</option><option value="bitpay">BitPay</option><option value="stripe">Stripe</option></select></td></tr>
    <tr><td>service:</td><td><select name="service" id="service"><option value="infinityWalletTest">infinityWalletTest</option></select></td></tr>
    <tr><td colspan="2"><input type="submit" onclick="doSubmit();return false"></td></tr>
</table>

<p> Curl request:<br /> <span id="a"></span></p>

<pre id="response"></pre>
<hr />
<div id="json_decode" style="width:800px"></div>

<div id="renew_list" style="width:800px"></div>
<div id="btn">
    <div id="dots">...</div><input value="Stop renew" type="button" onclick="doStopRenew()" id="renewBtn" disabled="disabled"><br />
    <br />
    <div id="stripebtns"><input type="button" onclick="doStripeSuccess()" value="Simulate Stripe's webhook as successful" />&nbsp;<input type="button" onclick="doStripeFail()" value="Simulate Stripe's webhook as error" /></div>
    <div id="coinbasebtns"><input type="button" onclick="doCoinbaseSuccess()" value="Simulate Coinbase's webhook as successful" /></div>
</div>
</body>
</html>
