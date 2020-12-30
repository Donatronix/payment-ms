<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Test for create invoice </title>

    <style type="text/css">

        td
        {border:1px solid silver}

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

    </style>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>

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

        function store_invoice()
        {

            $('#response').html('');
            $('#json_decode').html('');

            var user_id = document.getElementById('user_id').value;

            let parms = [ 'amount', 'currency', 'gateway' ];

            let _post = {};

            for(var i=0; i<parms.length; i++)
            {

                let val = document.getElementById(parms[i]).value;

                if(val != '')
                    _post[ parms[i] ] = val;
            }

            let arr = '';
            let first = '';

            for (let name in _post) {
                arr += first + name +'='+ _post[name];
                first = '&';
            }

            let url = '/v1/payments/charge';

            let curl = 'curl -X POST -H "user-id:'+user_id+'" -d "'+arr+'" {{$http}}://{{ $host }}'+url;

            let curl_decode = curl.replace(/&c/g,'&amp;c');

            $('#a').html(curl_decode);

            $.ajax({
                url: url,
                type: 'post',
                headers: {
                    'user-id': user_id
                },
                data: _post,
                dataType: 'html',
                success: function (data) {

                    $('#response').html(data);
                    $('#json_decode').html(renderJson(JSON.parse(data)));
                },
                error: function(xhr, status, error) {

                    let obj = JSON.parse(xhr.responseText);

                    console.log(obj);

                    let errorMessage = xhr.status + ': ' + xhr.statusText + ' ';

                    if( xhr.status == 422)
                    {
                        for( p in obj)
                            errorMessage += ' '+obj[p][0];
                    }
                    else
                        errorMessage += (obj.message != undefined) ? obj.message : obj.error;

                    let txt = '<span style="color:red">Error '+errorMessage;
                    txt += '</span>';

                    $('#response').html(txt);
                },

            });

        }

    </script>

</head>
<body>
<p><a href="/tests/payments">Tests contents</a></p>
<h1>test for create invoice </h1>

<table>
 <tr><td>user-id (to header)<span style="color:red;font-size:16pt">*</span></td><td> <input type="number" id="user_id" value="8" /></td></tr>

<tr><td>amount<span style="color:red;font-size:16pt">*</span></td><td> <input type="text" id="amount" value="10" /></td></tr>
<tr><td>currency<span style="color:red;font-size:16pt">*</span><br /></td><td> <input type="text" id="currency" value="USD" /></td></tr>
<tr><td>gateway<span style="color:red;font-size:16pt">*</span><br /></td><td> <input type="text" id="gateway" value="bitpay" /><input type="button" value="stripe" onclick="document.getElementById('gateway').value=this.value" /></td></tr>

</table>
<p> <input type="button" value="Test" onclick="store_invoice()" /> </p>

<p> Curl request:<br /> <span id="a"></span></p>

<div id="response" style="width:800px;word-wrap:break-word;word-break:break-all"></div>
<hr />
<div id="json_decode" style="width:800px"></div>

</body>
</html>
