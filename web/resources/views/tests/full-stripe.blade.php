<html lang="en">
<head>
    <title>Payment service. Full test.</title>
    <script src="https://js.stripe.com/v3/"></script>
    <script type="text/javascript">
        function doRedirect() {
            let params = window.location.search.replace('?','').split('&').reduce(function(p,e){var a = e.split('=');p[decodeURIComponent(a[0])] = decodeURIComponent(a[1]);return p;},{});
            let stripe = Stripe(params["pubkey"]);
            stripe.redirectToCheckout({sessionId: params["sessid"]});
        }
    </script>
</head>
<body onload="doRedirect()">
</body>
</html>
