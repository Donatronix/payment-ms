<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>test of list of systems</title>
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

            function list_systems() {

                let user_id = document.getElementById('user_id').value;

                let url = '/v1/payments/payments/systems' ;

                let curl = 'curl -X GET -H "user-id:'+user_id+'" {{$http}}://{{ $host }}' + url;

                let curl_decode = curl.replace(/&c/g, '&amp;c');

                $('#a').html(curl_decode);

                $.ajax({
                    url: url,
                    type: 'get',
                    headers: {
                        'user-id': user_id
                    },
                    dataType: 'html',
                    success: function (data) {
                        $('#response').html(data);
                        $('#json_decode').html(renderJson(JSON.parse(data)));
                    },
                    error: function (xhr, status, error) {
                        let obj = JSON.parse(xhr.responseText);

                        let errorMessage = xhr.status + ': ' + xhr.statusText + ' ';

                        errorMessage += (obj.message != undefined) ? obj.message : obj.error;

                        let txt = '<span style="color:red">Error '+errorMessage;
                        txt += '</span>';

                        $('#response').html(txt);
                    }
                });
            }
        </script>
    </head>
    <body>
        <p><a href="/tests/payments">Tests contents</a></p>
        <h1>test of balance list of user</h1>

        <table>
            <tr><td>user-id (to header)<span style="color:red;font-size:16pt">*</span></td><td> <input type="number" id="user_id" value="8" /></td></tr>
        </table>

        <p><input type="button" value="Test" onclick="list_systems()" /></p>
        <p> Curl request:<br /> <span id="a"></span></p>
        <div id="response" style="width:800px;word-wrap:break-word;word-break:break-all"></div>
        <hr />
        <div id="json_decode" style="width:800px"></div>
    </body>
</html>
