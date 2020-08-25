<?php

declare(strict_types=1);

namespace bizley\podium\api\repositories;

use bizley\podium\api\ars\AcquaintanceActiveRecord;
use bizley\podium\api\enums\AcquaintanceType;
use bizley\podium\api\interfaces\AcquaintanceRepositoryInterface;
use LogicException;
use Throwable;
use yii\db\StaleObjectException;

use function is_int;

final class AcquaintanceRepository implements AcquaintanceRepositoryInterface
{
    public string $activeRecordClass = AcquaintanceActiveRecord::class;

    private ?AcquaintanceActiveRecord $model = null;
    private array $errors = [];

    public function getModel(): AcquaintanceActiveRecord
    {
        if (null === $this->model) {
            throw new LogicException('You need to call fetchOne() or setModel() first!');
        }

        return $this->model;
    }

    public function setModel(?AcquaintanceActiveRecord $activeRecord): void
    {
        $this->model = $activeRecord;
    }

    public function fetchOne($memberId, $targetId): bool
    {
        /** @var AcquaintanceActiveRecord $modelClass */
        $modelClass = $this->activeRecordClass;
        /** @var AcquaintanceActiveRecord|null $model */
        $model = $modelClass::find()
            ->where(
                [
                    'member_id' => $memberId,
                    'target_id' => $targetId,
                ]
            )
            ->one();
        if (null === $model) {
            return false;
        }
        $this->model = $model;

        return true;
    }

    public function prepare($memberId, $targetId): void
    {
        /** @var AcquaintanceActiveRecord $acquaintance */
        $acquaintance = new $this->activeRecordClass();

        $acquaintance->member_id = $memberId;
        $acquaintance->target_id = $targetId;

        $this->model = $acquaintance;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function delete(): bool
    {
        return is_int($this->getModel()->delete());
    }

    public function befriend(): bool
    {
        $acquaintance = $this->getModel();
        $acquaintance->type_id = AcquaintanceType::FRIEND;
        if (!$acquaintance->validate()) {
            $this->errors = $acquaintance->errors;

            return false;
        }

        return $acquaintance->save(false);
    }

    public function ignore(): bool
    {
        $acquaintance = $this->getModel();
        $acquaintance->type_id = AcquaintanceType::IGNORE;
        if (!$acquaintance->validate()) {
            $this->errors = $acquaintance->errors;

            return false;
        }

        return $acquaintance->save(false);
    }

    public function isFriend(): bool
    {
        return AcquaintanceType::FRIEND === $this->getModel()->type_id;
    }

    public function isIgnoring(): bool
    {
        return AcquaintanceType::IGNORE === $this->getModel()->type_id;
    }
}
