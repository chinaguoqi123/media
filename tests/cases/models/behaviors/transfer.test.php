<?php
require_once CORE_TEST_CASES.DS.'libs'.DS.'model'.DS.'models.php';
require_once dirname(__FILE__) . DS . '..' . DS . '..' . DS . '..' . DS . 'fixtures' . DS . 'test_data.php';

class TransferBehaviorTestCase extends CakeTestCase {
	var $fixtures = array('core.image');
	
	function start() {
		parent::start();
		$this->TestData = new MediumTestData();
	}
	
	function end() {
		parent::end();
		$this->TestData->flushFiles();
		ClassRegistry::flush();
	}						
					
	function testDestinationFile() {
		$Model =& ClassRegistry::init('Image');		

		$Model->Behaviors->attach('Media.Transfer',array('destinationFile' => ':TMP::Source.basename:'));
		$file = $this->TestData->getFile(array('image-jpg.jpg' => TMP . 'wei?rd$Ö- FILE_name_'));
		$item = array('name' => 'Image xy','file' => $file);
		
		$Model->create();
		$result = $Model->save($item);
		$this->assertTrue($result);
		
		$file = $Model->getLastTransferredFile();
		unlink($file);	
		$this->assertEqual($file, TMP . 'wei_rd_oe_file_name');
		$Model->Behaviors->detach('Transfer');
		
		$Model->Behaviors->attach('Media.Transfer',array('destinationFile' => ':TMP::Idont.exist:'));
		$file = $this->TestData->getFile('image-jpg.jpg');
		$item = array('name' => 'Image xy','file' => $file);

		$Model->create();
		$this->expectError();
		$result = $Model->save($item);
		$this->assertFalse($result);
		$Model->Behaviors->detach('Transfer');
	}
	
	function testFileLocalToFileLocal() {
		$Model =& ClassRegistry::init('Image');		
		$Model->Behaviors->attach('Media.Transfer',array('destinationFile' => ':TMP::Source.basename:'));
		
		$file = $this->TestData->getFile('image-jpg.jpg');
		$item = array('name' => 'Image xy','file' => $file);

		$Model->create();
		$result = $Model->save($item);
		$this->assertTrue($result);
		$this->assertTrue(file_exists($file));
		
		$file = $Model->getLastTransferredFile();
		$this->assertTrue(file_exists($file));
		unlink($file);
	}
	
	function testFileLocalToFileLocalTableless() {
		$Model =& ClassRegistry::init('TheVoid');
		$Model->Behaviors->attach('Media.Transfer',array('destinationFile' => ':TMP::Source.basename:'));
		
		$file = $this->TestData->getFile('image-jpg.jpg');
		$Model->prepare($file);
		$result = $Model->perform();
		$this->assertTrue($result);
		$this->assertTrue(file_exists($file));

		$file = $Model->getLastTransferredFile();
		$this->assertTrue($file);
		$this->assertTrue(file_exists($file));
		unlink($file);
	}
	
	function testUrlRemoteToFileLocal() {
		$Model =& ClassRegistry::init('Image');		
		$Model->Behaviors->attach('Media.Transfer',array('destinationFile' => ':TMP::Source.basename:'));
				
		$item = array('name' => 'Image xy','file' => 'http://www.cakephp.org/img/cake-logo.png');
						
		$Model->create();
		$result = $Model->save($item);
		$this->assertTrue($result);
		
		$file = $Model->getLastTransferredFile();
		$this->assertTrue($file);
		$this->assertTrue(file_exists($file));
		unlink($file);
	}
}
?>