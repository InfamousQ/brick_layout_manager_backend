<?php
use BLMRA\Model\Plate;
use PHPUnit\Framework\TestCase;

class PlateTest extends TestCase {
	public function test_non_array_is_not_valid_constructor_arg () {
		$args = 123;
		$this->assertFalse(Plate::isValidContructor($args));
	}

	public function test_empty_array_is_not_valid_constructor_arg () {
		$args = array();
		$this->assertFalse(Plate::isValidContructor($args));
	}

	public function test_array_with_missing_keys_is_not_valid_constructor_arg () {
		$args = array('id' => 1, 'x' => 1, 'y' => 1);
		$this->assertFalse(Plate::isValidContructor($args));
	}

	public function test_array_with_negative_integers_is_not_valid_constructor_arg () {
		$args = array('id' => 1, 'x' => -1, 'y' => 1, 'z' => 1, 'height' => 1, 'width' => 1, 'color' => '#FFFFFF');
		$this->assertFalse(Plate::isValidContructor($args));
	}

	public function test_array_with_invalid_color_is_not_valid_constructor_arg () {
		$args = array('id' => 1, 'x' => 1, 'y' => 1, 'z' => 1, 'height' => 1, 'width' => 1, 'color' => 'FFFFFF');
		$this->assertFalse(Plate::isValidContructor($args));
	}

	public function test_array_with_color_with_incomplete_hex_is_not_valid_constructor_arg () {
		$args = array('id' => 1, 'x' => 1, 'y' => 1, 'z' => 1, 'height' => 1, 'width' => 1, 'color' => '#FFFF');
		$this->assertFalse(Plate::isValidContructor($args));
	}

	public function test_array_with_color_with_faulty_hex_is_not_valid_constructor_arg () {
		$args = array('id' => 1, 'x' => 1, 'y' => 1, 'z' => 1, 'height' => 1, 'width' => 1, 'color' => '#XXXXXX');
		$this->assertFalse(Plate::isValidContructor($args));
	}

	public function test_array_with_required_data_is_valid_constructor_arg () {
		$args = array('id' => 1, 'x' => 1, 'y' => 1, 'z' => 1, 'height' => 1, 'width' => 1, 'color' => '#FFFFFF');
		$this->assertTrue(Plate::isValidContructor($args));
	}
}