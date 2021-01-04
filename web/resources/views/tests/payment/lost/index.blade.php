<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>test of lost payments</title>
    <style type="text/css">
        #json_decode li {
            list-style-type: none;
        }

        #json_decode > ul > li {
            font-size: 13pt;
        }

        #json_decode > ul > li > ul > li > ul > li {
            font-size: 11pt;
        }

        #json_decode li > ul {
            border-left: 1px solid gray;
            margin-left: 20px;
            padding-left: 10px;
        }
    </style>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script type="text/javascript">
        function renderJson(jsonObject) {
            let html = '<ul>';
            for (var objType in jsonObject) {
                if (typeof jsonObject[objType] === 'object') {
                    html += '<li>' + objType + ':';
                    html += renderJson(jsonObject[objType]) + '</li>';
                } else {
                    html += '<li>' + objType + ': ' + jsonObject[objType] + '</li>';
                }
            }
            html += '</ul>';
            return html;
        }

        function doTest() {
            let url = '/v1/payments/admin/paymentslost';
            let curl = 'curl -X GET -H "user-id:2" {{$http}}://{{ $host }}';

            $.ajax({
                url: url,
                type: 'get',
                cache: false,
                headers: {
                    'user-id': 2
                },
                data: {
                    gateway: $("#gateway").val(),
                    limit: $("#limit").val(),
                    page: $("#page").val(),
                },
                beforeSend: function() {$('#a').html(curl + this.url)},
                dataType: 'html',
                success: function (data) {
                    $('#response').html(data);
                    $('#json_decode').html(renderJson(JSON.parse(data)));
                },
                error: function (xhr, status, error) {
                    let obj = JSON.parse(xhr.responseText);
                    let errorMessage = xhr.status + ': ' + xhr.statusText + ' ';
                    errorMessage += (obj.message != undefined) ? obj.message : obj.error;
                    let txt = '<span style="color:#ff0000">Error '+errorMessage;
                    txt += '</span>';
                    $('#response').html(txt);
                }
            });
        }
    </script>
</head>
<body>
<p><a href="/tests/payments">Tests contents</a></p>
<h1>test of lost payments</h1>

<table>
    <tr><td>gateway</td><td><select name="gateway" id="gateway"><option value="">all</option><option value="bitpay">BitPay</option><option value="coinbase">Coinbase</option><option value="paypal">PayPal</option><option value="stripe">Stripe</option></select></td></tr>
    <tr><td>units per page</td><td><input type="text" name="limit" id="limit" value="20"></td></tr>
    <tr><td>page</td><td><input type="text" name="page" id="page" value="1"></td></tr>
</table>

<p><input type="button" value="Test" onclick="doTest()" /></p>
<p> Curl request:<br /> <span id="a"></span></p>
<div id="response" style="width:800px;word-wrap:break-word;word-break:break-all"></div>
<hr />
<div id="json_decode" style="width:800px"></div>
</body>
</html>
