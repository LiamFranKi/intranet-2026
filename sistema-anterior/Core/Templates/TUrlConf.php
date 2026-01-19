<?php
$this->setUrlPatterns(Array(
	Array('^$','home:index'), 
	Array('^{Application}/?$', '{Application}:index'),
	Array('^{Application}/{Action}/?$','{Application}:{Action}'),
	Array('^{Application}/{Action}/{id}/?$','{Application}:{Action}'),
));
