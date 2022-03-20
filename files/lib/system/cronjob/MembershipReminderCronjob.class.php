<?php

namespace shop\system\cronjob;

use shop\data\membership\Membership;
use shop\data\membership\MembershipAction;
use shop\data\membership\MembershipList;
use wcf\data\conversation\ConversationAction;
use wcf\data\cronjob\Cronjob;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\cronjob\AbstractCronjob;
use wcf\system\WCF;
use wcf\util\DateUtil;
use wcf\util\StringUtil;

/**
 * Remind members about expiring memberships.
 *
 * @author	Joshua Ruesweg
 * @copyright	2016-2022 WCFLabs.de
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class MembershipReminderCronjob extends AbstractCronjob
{
	/**
	 * @inheritDoc
	 */
	public function execute(Cronjob $cronjob)
	{
		parent::execute($cronjob);

		if (empty(SHOP_MEMBERSHIP_REMINDER_TEXT) || empty(SHOP_MEMBERSHIP_REMINDER_TITLE)) return;

		$membershipList = new MembershipList();
		$membershipList->getConditionBuilder()->add('isDisabled = ?', [0]);
		$membershipList->getConditionBuilder()->add('validTo < ?', [strtotime('+' . SHOP_MEMBERSHIP_REMINDER_REMIND_DAYS_BEFORE . ' days', TIME_NOW)]);
		$membershipList->getConditionBuilder()->add('remindedToDate < validTo');
		$membershipList->readObjects();

		try {
			WCF::getDB()->beginTransaction();

			/** @var Membership $membership */
			foreach ($membershipList as $membership) {
				UserProfileRuntimeCache::getInstance()->cacheObjectID($membership->userID);
			}

			/** @var Membership $membership */
			foreach ($membershipList as $membership) {
				$userProfile = UserProfileRuntimeCache::getInstance()->getObject($membership->userID);

				(new ConversationAction([], 'create', [
					'data' => [
						'userID' => null,
						'username' => 'System',
						'time' => TIME_NOW,
						'subject' => $userProfile->getLanguage()->get(SHOP_MEMBERSHIP_REMINDER_TITLE),
						'isClosed' => 1
					],
					'participants' => [
						$membership->userID
					],
					'messageData' => [
						'message' => str_replace([
							'{$username}',
							'{$validTo}',
						], [
							StringUtil::encodeHTML($userProfile->username),
							DateUtil::format(DateUtil::getDateTimeByTimestamp($membership->validTo), DateUtil::DATE_FORMAT, $userProfile->getLanguage(), $userProfile->getDecoratedObject())
						], $userProfile->getLanguage()->get(SHOP_MEMBERSHIP_REMINDER_TEXT))
					]
				]))->executeAction();

				$membershipAction = new MembershipAction($membershipList->getObjects(), 'update', [
					'data' => [
						'remindedToDate' => $membership->validTo
					]
				]);
				$membershipAction->executeAction();
			}

			WCF::getDB()->commitTransaction();
		} catch (\Exception $e) {
			WCF::getDB()->rollBackTransaction();

			throw $e;
		}
	}
}
