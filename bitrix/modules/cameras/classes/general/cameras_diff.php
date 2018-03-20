<?

IncludeModuleLangFile(__FILE__);

class CCamerasDiff
{
	/**
	 * @deprecated Use Bitrix\Cameras\Diff::getDiffHtml() instead.
	 * @param string $a First version of text to be compared.
	 * @param string $b Second version of text to be compared.
	 * @return string Formatted result of comparison.
	 */
	public static function getDiff($a, $b)
	{
		$diff = new Bitrix\Cameras\Diff();
		return $diff->getDiffHtml($a, $b);
	}
}
