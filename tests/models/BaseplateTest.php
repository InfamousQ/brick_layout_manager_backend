<?php
use BLMRA\Model\Baseplate;
use PHPUnit\Framework\TestCase;

class BaseplateTest extends TestCase {
	public function test_non_array_is_not_valid_constructor_arg () {
		$args = 123;
		$this->assertFalse(Baseplate::isValidContructor($args));
	}

	public function test_empty_array_is_not_valid_constructor_arg () {
		$args = array();
		$this->assertFalse(Baseplate::isValidContructor($args));
	}

	public function test_array_with_negative_id_is_not_valid_constructor_arg () {
		$args = array('id' => -1, 'plates' => 123);
		$this->assertFalse(Baseplate::isValidContructor($args));
	}

	public function test_array_with_no_plates_array_is_not_valid_constructor_arg () {
		$args = array('id' => 1, 'plates' => 123);
		$this->assertFalse(Baseplate::isValidContructor($args));
	}

	public function test_array_with_empty_plates_array_is_not_valid_constructor_arg () {
		$args = array('id' => 1, 'plates' => array());
		$this->assertFalse(Baseplate::isValidContructor($args));
	}

	public function test_array_with_valid_data_is_valid_constructor_arg () {
		$args = array(
			'id' => 1,
			'plates' => array(
				array('id' => 1, 'x' => 1, 'y' => 1, 'z' => 1, 'height' => 1, 'width' => 1, 'color' => '#FFFFFF'),
				)
		);
		$this->assertTrue(Baseplate::isValidContructor($args));
	}
}