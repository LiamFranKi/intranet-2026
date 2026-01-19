<?php
foreach(['blurred', 'polygon', 'abstract'] As $type){
	for($i = 1; $i <= 16; $i++){
		file_put_contents($type.'/bg/'.$i.'.jpg', file_get_contents('http://www.themeon.net/nifty/v2.9.1/premium/boxed-bg/'.$type.'/bg/'.$i.'.jpg'));
	}
}
