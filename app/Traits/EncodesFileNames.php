<?php


namespace App\Traits;


use Illuminate\Support\Facades\Date;

trait EncodesFileNames
{
	/**
	 * @param string $fileName
	 * @return string
	 */
	public function getEncodedFileName(string $fileName): string
	{
		$ext = substr($fileName, strrpos($fileName, '.'));
		$ts = Date::now()->getTimestamp();
		return base64_encode("{$this->id}_$ts") . $ext;
	}
}
