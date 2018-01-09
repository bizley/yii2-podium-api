<?php

namespace bizley\podium\api\components;

use bizley\podium\api\dictionaries\Acquaintance;

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

    public function ignore($member, $target)
    {

    }

    public function unignore()
    {

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

    public function befriend()
    {

    }

    public function unfriend()
    {

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
