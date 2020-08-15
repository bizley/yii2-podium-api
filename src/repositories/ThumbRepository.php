<?php

declare(strict_types=1);

namespace bizley\podium\api\repositories;

use bizley\podium\api\ars\ThumbActiveRecord;
use bizley\podium\api\interfaces\ThumbRepositoryInterface;
use LogicException;
use Throwable;
use yii\db\StaleObjectException;

final class ThumbRepository implements ThumbRepositoryInterface
{
    public string $activeRecordClass = ThumbActiveRecord::class;

    private ?ThumbActiveRecord $model = null;
    private array $errors = [];

    public function getModel(): ThumbActiveRecord
    {
        if (null === $this->model) {
            throw new LogicException('You need to call fetchOne() or setModel() first!');
        }

        return $this->model;
    }

    public function setModel(?ThumbActiveRecord $activeRecord): void
    {
        $this->model = $activeRecord;
    }

    public function prepare($memberId, $postId): void
    {
        /** @var ThumbActiveRecord $thumb */
        $thumb = new $this->activeRecordClass();

        $thumb->member_id = $memberId;
        $thumb->post_id = $postId;
        $thumb->thumb = 0;

        $this->model = $thumb;
    }

    public function fetchOne($memberId, $postId): bool
    {
        /** @var ThumbActiveRecord $modelClass */
        $modelClass = $this->activeRecordClass;
        /** @var ThumbActiveRecord|null $model */
        $model = $modelClass::find()
            ->where(
                [
                    'member_id' => $memberId,
                    'post_id' => $postId,
                ]
            )
            ->one();
        if (null === $model) {
            return false;
        }
        $this->model = $model;

        return true;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function reset(): bool
    {
        return is_int($this->getModel()->delete());
    }

    public function isUp(): bool
    {
        return 1 === $this->getModel()->thumb;
    }

    public function isDown(): bool
    {
        return -1 === $this->getModel()->thumb;
    }

    public function up(): bool
    {
        $thumb = $this->getModel();
        $thumb->thumb = 1;
        if (!$thumb->validate()) {
            $this->errors = $thumb->errors;
            return false;
        }

        return $thumb->save(false);
    }

    public function down(): bool
    {
        $thumb = $this->getModel();
        $thumb->thumb = -1;
        if (!$thumb->validate()) {
            $this->errors = $thumb->errors;
        }

        return $thumb->save(false);
    }
}
