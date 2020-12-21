<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Test of show order </title>

    <style type="text/css">
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

        function show_version()
        {

            let url = '/v1/infinity-wallet/version';

            let curl = 'curl -X GET  {{$http}}://{{ $host }}'+url;

            let curl_decode = curl.replace(/&c/,'&amp;c');

            $('#a').html(curl_decode);

            $.ajax({
                url: url,
                type: 'get',
                dataType: 'html',
                success: function (data) {

                    $('#response').html(data);
                    $('#json_decode').html(renderJson(JSON.parse(data)));
                },
                error: function(xhr, status, error) {
                    let obj = JSON.parse(xhr.responseText);
                    var errorMessage = xhr.status + ': ' + xhr.statusText +' ' + obj.error;
                    var txt = '<span style="color:red">Error '+errorMessage;
                    if(errorMessage == '422: Unprocessable Entity')
                        txt+= ' (may be validation error, try send CURL-request)';
                    txt += '</span>';

                    $('#response').html(txt);
                },

            });
        }

    </script>

</head>
<body>

<p><a href="/tests/infinity-wallet">Contents</a></p>

<h1>Test of version</h1>

<p> <input type="button" value="Test" onclick="show_version()" /> </p>

<p> Curl request:<br /> <span id="a"></span></p>

<pre id="response"></pre>
<hr />
<div id="json_decode" style="width:800px"></div>
</body>
</html>
