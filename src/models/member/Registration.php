<?php

declare(strict_types=1);

namespace bizley\podium\api\models\member;

use bizley\podium\api\base\PodiumResponse;
use bizley\podium\api\enums\MemberStatus;
use bizley\podium\api\events\RegistrationEvent;
use bizley\podium\api\interfaces\RegistrationInterface;
use bizley\podium\api\repos\MemberRepo;
use Yii;
use yii\behaviors\SluggableBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * Class Registration
 * @package bizley\podium\api\models\member
 */
class Registration extends MemberRepo implements RegistrationInterface
{
    public const EVENT_BEFORE_REGISTERING = 'podium.member.registering.before';
    public const EVENT_AFTER_REGISTERING = 'podium.member.registering.after';

    /**
     * @return array
     */
    public function behaviors(): array
    {
        return [
            'timestamp' => TimestampBehavior::class,
            'slug' => [
                'class' => SluggableBehavior::class,
                'attribute' => 'username',
                'ensureUnique' => true,
            ],
        ];
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            [['user_id', 'username'], 'required'],
            [['user_id', 'username'], 'string', 'max' => 255],
            [['user_id', 'username'], 'unique'],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels(): array
    {
        return [
            'user_id' => Yii::t('podium.label', 'registration.user.id'),
            'username' => Yii::t('podium.label', 'registration.username'),
        ];
    }

    /**
     * @param array|null $data
     * @return bool
     */
    public function loadData(?array $data = null): bool
    {
        return $this->load($data, '');
    }

    /**
     * @return bool
     */
    public function beforeRegister(): bool
    {
        $event = new RegistrationEvent();
        $this->trigger(self::EVENT_BEFORE_REGISTERING, $event);

        return $event->canRegister;
    }

    /**
     * @return PodiumResponse
     */
    public function register(): PodiumResponse
    {
        if (!$this->beforeRegister()) {
            return PodiumResponse::error();
        }

        $this->status_id = MemberStatus::REGISTERED;

        if (!$this->save()) {
            Yii::error(['Error while registering member', $this->errors], 'podium');
            return PodiumResponse::error($this);
        }

        $this->afterRegister();

        return PodiumResponse::success();
    }

    public function afterRegister(): void
    {
        $this->trigger(self::EVENT_AFTER_REGISTERING, new RegistrationEvent(['model' => $this]));
    }
}
