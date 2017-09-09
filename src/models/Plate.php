<?php
namespace BLMRA\Model;

class Plate {
	public $id;
	public $x;
	public $y;
	public $z;
	public $height;
	public $width;
	public $color;

	public static function isValidContructor ($args) {
		if (!is_array($args)) {
			return false;
		}

		// Required fields exist
		$required_fields = array('id', 'x', 'y', 'z', 'height', 'width', 'color');
		foreach ($required_fields as $f)  {
			if (empty($args[$f])) {
				return false;
			}
		}


		// Check integer fields are valid (int + 0 or larger)
		$integer_fields = array('id', 'x', 'y', 'z', 'height', 'width');
		foreach ($integer_fields as $f) {
			if (!is_numeric($args[$f])) {
				return false;
			}

			$$f = (int) $args[$f];
			if ($$f < 0) {
				return false;
			}
		}

		// Check that color is a hex number (string that starts with # and has 6 letters that form hex-number)
		$hex = $args['color'];
		if ('#' !== substr($hex, 0, 1)) {
			return false;
		}
		$hex = ltrim($hex, '#');
		if (strlen($hex) !== 6) {
			return false;
		}
		if (!ctype_xdigit($hex)) {
			return false;
		}

		return true;
	}
}