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

    public function delete()
    {

    }

    public function search($filter)
    {

    }

    /**
     * Checks whether member of given ID ignores target member of given ID.
     * @param int $member
     * @param int $target
     * @return bool
     */
    public function isIgnoring($member, $target)
    {
        return $this->acquaintanceRepo->check(['member_id' => $member, 'target_id' => $target, 'type' => Acquaintance::IGNORE]);
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
     * @param int $member
     * @param int $target
     * @return bool
     * @throws \Exception
     */
    public function ignore($member, $target)
    {
        if (!$this->beforeIgnore()) {
            return false;
        }
        $this->podium->access->check($member, Permission::MEMBER_ACQUAINTANCE);

        $conditions = [
            'member_id' => $member,
            'target_id' => $target,
            'type' => Acquaintance::IGNORE
        ];
        if ($this->isIgnoring($member, $target)) {
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
     * @param int $member
     * @param int $target
     * @return bool
     */
    public function isFriendWith($member, $target)
    {
        return $this->acquaintanceRepo->check(['member_id' => $member, 'target_id' => $target, 'type' => Acquaintance::FRIEND]);
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
     * @param int $member
     * @param int $target
     * @return bool
     * @throws \Exception
     */
    public function friend($member, $target)
    {
        if (!$this->beforeFriend()) {
            return false;
        }
        $this->podium->access->check($member, Permission::MEMBER_ACQUAINTANCE);

        $conditions = [
            'member_id' => $member,
            'target_id' => $target,
            'type' => Acquaintance::FRIEND
        ];
        if ($this->isFriendWith($member, $target)) {
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

    public function view()
    {

    }

    public function ban()
    {

    }

    public function unban()
    {

    }

    public function promote()
    {

    }

    public function demote()
    {

    }
}
