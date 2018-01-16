<?php

namespace bizley\podium\api\components;

use bizley\podium\api\dictionaries\Acquaintance;
use bizley\podium\api\dictionaries\Permission;

/**
 * Class Member
 * @package bizley\podium\api\components
 *
 * @property \bizley\podium\api\repositories\Member $memberRepo
 * @property \bizley\podium\api\repositories\Acquaintance $acquaintanceRepo
 */
class Member extends Component
{
    const EVENT_BEFORE_REGISTER = 'member.register.before';
    const EVENT_AFTER_REGISTER = 'member.register.after';
    const EVENT_BEFORE_DELETE = 'member.delete.before';
    const EVENT_AFTER_DELETE = 'member.delete.after';
    const EVENT_BEFORE_IGNORE = 'member.ignore.before';
    const EVENT_AFTER_IGNORE = 'member.ignore.after';
    const EVENT_BEFORE_FRIEND = 'member.friend.before';
    const EVENT_AFTER_FRIEND = 'member.friend.after';

    /**
     * @return bool
     */
    public function beforeRegister()
    {
        $event = new PodiumEvent();
        $this->trigger(self::EVENT_BEFORE_REGISTER, $event);
        return $event->isValid;
    }

    /**
     * Registers new member.
     * @param $data
     * @return bool
     * @throws \Exception
     */
    public function register($data)
    {
        if (!$this->beforeRegister()) {
            return false;
        }
        $result = $this->memberRepo->store($data);

        $this->afterRegister();
        return $result;
    }

    /**
     *
     */
    public function afterRegister()
    {
        $this->trigger(self::EVENT_AFTER_REGISTER);
    }

    /**
     * @return bool
     */
    public function beforeDelete()
    {
        $event = new PodiumEvent();
        $this->trigger(self::EVENT_BEFORE_DELETE, $event);
        return $event->isValid;
    }

    /**
     * Deletes member.
     * @param $memberId
     * @return false|int
     * @throws \Exception
     * @throws \bizley\podium\api\repositories\RepoNotFoundException
     */
    public function delete($memberId)
    {
        if (!$this->beforeDelete()) {
            return false;
        }
        $result = $this->memberRepo->fetch($memberId)->remove();

        $this->afterDelete();
        return $result;
    }

    /**
     *
     */
    public function afterDelete()
    {
        $this->trigger(self::EVENT_AFTER_DELETE);
    }

    public function search($filter)
    {

    }

    /**
     * Checks whether member of given ID ignores target member of given ID.
     * @param int $memberId
     * @param int $targetId
     * @return bool
     */
    public function isIgnoring($memberId, $targetId)
    {
        return $this->acquaintanceRepo->check(['member_id' => $memberId, 'target_id' => $targetId, 'type' => Acquaintance::IGNORE]);
    }

    /**
     * @return bool
     */
    public function beforeIgnore()
    {
        $event = new PodiumEvent();
        $this->trigger(self::EVENT_BEFORE_IGNORE, $event);
        return $event->isValid;
    }

    /**
     * Changes ignoring status between member and his target.
     * @param int $memberId
     * @param int $targetId
     * @return bool
     * @throws \Exception
     */
    public function ignore($memberId, $targetId)
    {
        if (!$this->beforeIgnore()) {
            return false;
        }
        $this->podium->access->check($memberId, Permission::MEMBER_ACQUAINTANCE);

        $conditions = [
            'member_id' => $memberId,
            'target_id' => $targetId,
            'type' => Acquaintance::IGNORE
        ];
        if ($this->isIgnoring($memberId, $targetId)) {
            $result = $this->acquaintanceRepo->fetch($conditions)->remove();
        } else {
            $result = $this->acquaintanceRepo->store($conditions);
        }

        $this->afterIgnore();
        return $result;
    }

    /**
     *
     */
    public function afterIgnore()
    {
        $this->trigger(self::EVENT_AFTER_IGNORE);
    }

    /**
     * Checks whether member of given ID is friend with target member of given ID.
     * @param int $memberId
     * @param int $targetId
     * @return bool
     */
    public function isFriendWith($memberId, $targetId)
    {
        return $this->acquaintanceRepo->check(['member_id' => $memberId, 'target_id' => $targetId, 'type' => Acquaintance::FRIEND]);
    }

    /**
     * @return bool
     */
    public function beforeFriend()
    {
        $event = new PodiumEvent();
        $this->trigger(self::EVENT_BEFORE_FRIEND, $event);
        return $event->isValid;
    }

    /**
     * Changes friend status between member and his target.
     * @param int $memberId
     * @param int $targetId
     * @return bool
     * @throws \Exception
     */
    public function friend($memberId, $targetId)
    {
        if (!$this->beforeFriend()) {
            return false;
        }
        $this->podium->access->check($memberId, Permission::MEMBER_ACQUAINTANCE);

        $conditions = [
            'member_id' => $memberId,
            'target_id' => $targetId,
            'type' => Acquaintance::FRIEND
        ];
        if ($this->isFriendWith($memberId, $targetId)) {
            $result = $this->acquaintanceRepo->fetch($conditions)->remove();
        } else {
            $result = $this->acquaintanceRepo->store($conditions);
        }

        $this->afterFriend();
        return $result;
    }

    /**
     *
     */
    public function afterFriend()
    {
        $this->trigger(self::EVENT_AFTER_FRIEND);
    }

    public function view($memberId)
    {

    }
}
