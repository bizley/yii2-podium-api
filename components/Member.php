<?php

namespace bizley\podium\api\components;

use bizley\podium\api\repositories\RepoEvent;

/**
 * Class Member
 * @package bizley\podium\api\components
 *
 * @property \bizley\podium\api\repositories\Member memberRepo
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
        $event = new RepoEvent();
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

    public function ignore()
    {

    }

    public function unignore()
    {

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
