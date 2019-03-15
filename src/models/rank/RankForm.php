<?php

declare(strict_types=1);

namespace bizley\podium\api\models\rank;

use bizley\podium\api\base\PodiumResponse;
use bizley\podium\api\events\ModelEvent;
use bizley\podium\api\interfaces\ModelFormInterface;
use bizley\podium\api\repos\RankRepo;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * Class RankForm
 * @package bizley\podium\api\models\rank
 */
class RankForm extends RankRepo implements ModelFormInterface
{
    public const EVENT_BEFORE_CREATING = 'podium.rank.creating.before';
    public const EVENT_AFTER_CREATING = 'podium.rank.creating.after';
    public const EVENT_BEFORE_EDITING = 'podium.rank.editing.before';
    public const EVENT_AFTER_EDITING = 'podium.rank.editing.after';

    /**
     * @return array
     */
    public function behaviors(): array
    {
        return ['timestamp' => TimestampBehavior::class];
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            [['name', 'min_posts'], 'required'],
            [['name'], 'string', 'max' => 255],
            [['min_posts'], 'integer', 'min' => 0],
            [['min_posts'], 'unique'],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels(): array
    {
        return [
            'name' => Yii::t('podium.label', 'rank.name'),
            'min_posts' => Yii::t('podium.label', 'rank.minimum.posts'),
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
    public function beforeCreate(): bool
    {
        $event = new ModelEvent();
        $this->trigger(self::EVENT_BEFORE_CREATING, $event);

        return $event->canCreate;
    }

    /**
     * @return PodiumResponse
     */
    public function create(): PodiumResponse
    {
        if (!$this->beforeCreate()) {
            return PodiumResponse::error();
        }

        if (!$this->save()) {
            Yii::error(['Error while creating rank', $this->errors], 'podium');
            return PodiumResponse::error($this);
        }

        $this->afterCreate();

        return PodiumResponse::success(['id' => $this->id]);
    }

    public function afterCreate(): void
    {
        $this->trigger(self::EVENT_AFTER_CREATING, new ModelEvent(['model' => $this]));
    }

    /**
     * @return bool
     */
    public function beforeEdit(): bool
    {
        $event = new ModelEvent();
        $this->trigger(self::EVENT_BEFORE_EDITING, $event);

        return $event->canEdit;
    }

    /**
     * @return PodiumResponse
     */
    public function edit(): PodiumResponse
    {
        if (!$this->beforeEdit()) {
            return PodiumResponse::error();
        }

        if (!$this->save()) {
            Yii::error(['Error while editing rank', $this->errors], 'podium');
            return PodiumResponse::error($this);
        }

        $this->afterEdit();

        return PodiumResponse::success();
    }

    public function afterEdit(): void
    {
        $this->trigger(self::EVENT_AFTER_EDITING, new ModelEvent(['model' => $this]));
    }
}
