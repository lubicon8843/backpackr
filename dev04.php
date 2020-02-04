<?php
// 문제 4) 아래 두 개의 Class의 공통점을 찾아 개선하는 코드를 작성하세요.

class FastSpeed
{

	protected $config;

	public function __construct($config)
	{
		$this->setConfig($config);
	}

	protected function setConfig($config)
	{
		if (!is_array($config)) {
			throw new \Exception('Invalid config');
		}

		$this->config = $config;
	}

	public function goFast()
	{
		print_r($this->config);
	}

}

class FastestSpeed extends FastSpeed
{

	public function __construct($setting)
	{
		parent::__construct($setting);
	}
	public function goFastest()
	{
		print_r($this->config);
	}

}


$Fast = new FastSpeed(array('test'=>'Y'));
$Fast->goFast();

$Fasttest = new FastestSpeed(array('test2'=>'Y'));
$Fasttest->goFastest();
?>