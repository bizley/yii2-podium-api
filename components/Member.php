<?php

namespace bizley\podium\api\components;

use bizley\podium\api\repositories\RepoEvent;

class Member extends Component
{
    const EVENT_BEFORE_REGISTER = 'beforeRegister';
    const EVENT_AFTER_REGISTER = 'afterRegister';

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
        $result = $this->repo->store($data);

        $this->afterRegister();
        return $result;
    }

    public function afterRegister()
    {
        $this->trigger(self::EVENT_AFTER_REGISTER);
    }
}