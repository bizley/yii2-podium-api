<?php

declare(strict_types=1);

namespace bizley\podium\api\repositories;

use bizley\podium\api\ars\MemberActiveRecord;
use bizley\podium\api\ars\ThreadActiveRecord;
use bizley\podium\api\enums\MemberStatus;
use bizley\podium\api\interfaces\MemberRepositoryInterface;
use bizley\podium\api\interfaces\RepositoryInterface;
use LogicException;
use yii\base\NotSupportedException;

final class MemberRepository implements MemberRepositoryInterface
{
    use ActiveRecordRepositoryTrait;

    public string $activeRecordClass = MemberActiveRecord::class;

    private ?MemberActiveRecord $model = null;

    public function getActiveRecordClass(): string
    {
        return $this->activeRecordClass;
    }

    public function getModel(): MemberActiveRecord
    {
        if (null === $this->model) {
            throw new LogicException('You need to call fetchOne() or setModel() first!');
        }

        return $this->model;
    }

    public function setModel(?MemberActiveRecord $activeRecord): void
    {
        $this->model = $activeRecord;
    }

    public function getId(): int
    {
        return $this->getModel()->id;
    }

    /**
     * @throws NotSupportedException
     */
    public function getParent(): RepositoryInterface
    {
        throw new NotSupportedException('Member does not have parent!');
    }

    public function create(array $data, $authorId, $forumId, $categoryId): bool
    {
        /** @var ThreadActiveRecord $thread */
        $thread = new $this->activeRecordClass();
        if (!$thread->load($data, '')) {
            return false;
        }

        $thread->author_id = $authorId;
        $thread->forum_id = $forumId;
        $thread->category_id = $categoryId;

        if (!$thread->validate()) {
            $this->errors = $thread->errors;
            return false;
        }

        return $thread->save(false);
    }

    public function ban(): bool
    {
        $member = $this->getModel();
        $member->status_id = MemberStatus::BANNED;
        if (!$member->validate()) {
            $this->errors = $member->errors;

            return false;
        }

        return $member->save(false);
    }

    public function unban(): bool
    {
        $member = $this->getModel();
        $member->status_id = MemberStatus::ACTIVE;
        if (!$member->validate()) {
            $this->errors = $member->errors;

            return false;
        }

        return $member->save(false);
    }
}
