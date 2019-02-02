<!doctype html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
</head>
<body>
尊敬的 {{ $name }} 用户，
<br>
{{--<a href="{{ URL('mailBox?uid='.$uid.'&activationcode='.$activationcode.'') }}" target="_blank">--}}
<a href="http://niuyueyang.picp.io:18863/laravel55/public/mailBox/{{ $uid }}/{{$activationcode}}" target="_blank">
    请点击此处激活XXX账号
</a>
</body>
</html>
