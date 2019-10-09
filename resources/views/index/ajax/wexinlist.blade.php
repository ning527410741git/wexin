<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Document</title>
</head>
<body>
	<table>
		<tr>
			<td>用户的昵称</td>
			<td>用户的性别</td>
			<td>用户所在城市</td>
			<td>用户头像</td>
			<td>用户关注时间</td>
		</tr>

	@foreach($dtinfo as $v)
		<tr>
			<td>{{ $v['nickname'] }}</td>
			<td>{{ $v['sex'] }}</td>
			<td>{{ $v['city'] }}</td>
			<td><img src="{{ $v['headimgurl'] }}" alt=""></td>
			<td>{{ date('Y-m-d H:i:s',$v['subscribe_time']) }}</td>
		</tr>
	@endforeach

	</table>
</body>
</html>