<?php

$r = new Redis();

$r->connect('127.0.0.1');

$path = dirname($_SERVER['REQUEST_URI']);
if($path != '/') $path = $path.'/';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
	if($_SERVER['CONTENT_TYPE'] == 'application/json') {
		$raw = file_get_contents("php://input");
		$_POST = json_decode($raw, true);
	}

	if($r->sismember("keys.{$_SERVER['HTTP_HOST']}", $_POST['key'])) {
		$r->incr("id.{$_SERVER['HTTP_HOST']}");
		$n = (int)$r->get("id.{$_SERVER['HTTP_HOST']}");
		$id = base_convert($n, 10, 36);
		$r->set("{$_SERVER['HTTP_HOST']}/$id", $_POST['longUrl']);
		$ret = array('id' => $id, 'longUrl' => $_POST['longUrl'], 'kind' => 'urlshortener#url');
		if($_SERVER['HTTP_ACCEPT'] == 'application/json') {
			header('Content-Type: application/json');
			print(json_encode($ret));
		} else {
			print("Your shortened URL is <a href='http://{$_SERVER['HTTP_HOST']}$path$id'>http://{$_SERVER['HTTP_HOST']}$path$id</a>");
		}
	} else {
		header("HTTP/1.1 403 Forbidden");
		die("Forbidden");
	}


} else {
	if($_GET['id']) {
		$v = $r->get("{$_SERVER['HTTP_HOST']}/{$_GET['id']}");
		header("Location: $v");
	} else { ?>
		<form action='index.php' method='post' id='shortener'>
			Key: <input type='password' name='key'><br>
			URL: <input type='text' name='longUrl'><br>
			<input type='submit'>
		</form>
	<?php }
}

?>
