<?
/**
 * @access private
 */

namespace Bitrix\Cameras\Integration\SocialNetwork;

use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Socialnetwork\Item\LogIndex;

class Log
{
	const EVENT_ID_CAMERAS = 'cameras';

	/**
	 * Returns set EVENT_ID processed by handler to generate content for full index.
	 *
	 * @param void
	 * @return array
	 */
	public static function getEventIdList()
	{
		return array(
			self::EVENT_ID_CAMERAS
		);
	}

	/**
	 * Returns content for LogIndex.
	 *
	 * @param Event $event Event from LogIndex::setIndex().
	 * @return EventResult
	 */
	public static function onIndexGetContent(Event $event)
	{
		static $camerasParser = null;

		$result = new EventResult(
			EventResult::UNDEFINED,
			array(),
			'cameras'
		);

		$eventId = $event->getParameter('eventId');
		$sourceId = $event->getParameter('sourceId');

		if (!in_array($eventId, self::getEventIdList()))
		{
			return $result;
		}

		$content = "";
		$element = false;

		if (intval($sourceId) > 0)
		{
			$element = \CCameras::getElementById($sourceId, array(
				'CHECK_PERMISSIONS' => 'N',
				'ACTIVE' => 'Y'
			));
		}

		if ($element)
		{
			if (!$camerasParser)
			{
				$camerasParser = new \CCamerasParser();
			}

			$element['DETAIL_TEXT'] = $camerasParser->parse($element['DETAIL_TEXT'], $element['DETAIL_TEXT_TYPE'], array());
			$element['DETAIL_TEXT'] = $camerasParser->clear($element['DETAIL_TEXT']);

			$content .= LogIndex::getUserName($element["CREATED_BY"])." ";
			$content .= $element['NAME']." ";
			$content .= \CTextParser::clearAllTags($element['DETAIL_TEXT']);
		}

		$result = new EventResult(
			EventResult::SUCCESS,
			array(
				'content' => $content,
			),
			'cameras'
		);

		return $result;
	}


}