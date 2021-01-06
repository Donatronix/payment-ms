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

        #btn {
            animation-name: bg;
            animation-duration: 4s;
            animation-iteration-count: infinite;
            padding: 3px;
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
                lastPayment = str;
                $("#renew_list").append("<br /><br /><pre>"+str+"</pre><hr />"+renderJson(data));
                window.scrollTo(0, document.body.scrollHeight);
            }
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
                complete: function(data){console.log(data)},
                error: function(data){alert(data.responseText)},
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
            }
        }

        function doStripe(key,sessid) {
            window.open("full-stripe?pubkey="+encodeURIComponent(key)+"&sessid="+encodeURIComponent(sessid), "_blank");
        }

        function doAnswer(data) {
            console.log(data);
            if(data.status=="error") {
                console.log(data.message);
                alert(data.message);
                return;
            }
            payment_id = data.payment_id;
            $("#renewBtn").prop("disabled", false);
            $("#btn").show();
            startInterval();
            if(data.gateway=="stripe") {
                doStripe(data.stripe_pubkey, data.session_id);
            } else {
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
<span id="btn"><input value="Stop renew payment" type="button" onclick="doStopRenew()" id="renewBtn" disabled="disabled"></span>

</body>
</html>
