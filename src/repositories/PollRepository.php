<?php

declare(strict_types=1);

namespace bizley\podium\api\repositories;

use bizley\podium\api\ars\PollActiveRecord;
use bizley\podium\api\enums\PollChoice;
use bizley\podium\api\interfaces\PollAnswerRepositoryInterface;
use bizley\podium\api\interfaces\PollRepositoryInterface;
use bizley\podium\api\interfaces\PollVoteRepositoryInterface;
use bizley\podium\api\interfaces\RepositoryInterface;
use LogicException;

final class PollRepository implements PollRepositoryInterface
{
    use ActiveRecordRepositoryTrait;

    public string $activeRecordClass = PollActiveRecord::class;

    private ?PollActiveRecord $model = null;

    public function getActiveRecordClass(): string
    {
        return $this->activeRecordClass;
    }

    public function getModel(): PollActiveRecord
    {
        if (null === $this->model) {
            throw new LogicException('You need to call fetchOne() or setModel() first!');
        }

        return $this->model;
    }

    public function setModel(?PollActiveRecord $activeRecord): void
    {
        $this->model = $activeRecord;
    }

    public function getId(): int
    {
        return $this->getModel()->id;
    }

    public function getParent(): RepositoryInterface
    {
        $threadRepository = $this->getModel()->thread;
        $parent = new ThreadRepository();
        $parent->setModel($threadRepository);

        return $parent;
    }

    public function isArchived(): bool
    {
        return $this->getModel()->archived;
    }

    public function create(array $data, array $answers, $authorId, $threadId): bool
    {
        /** @var PollActiveRecord $poll */
        $poll = new $this->activeRecordClass();
        if (!$poll->load($data, '')) {
            return false;
        }

        $poll->author_id = $authorId;
        $poll->thread_id = $threadId;

        if (!$poll->save()) {
            $this->errors = $poll->errors;
            return false;
        }

        $this->model = $poll;

        $answerRepository = $this->getAnswerRepository();
        foreach ($answers as $answer) {
            if (!$answerRepository->create($answer)) {
                return false;
            }
        }

        return true;
    }

    public function move($threadId): bool
    {
        $poll = $this->getModel();
        $poll->thread_id = $threadId;
        if (!$poll->validate()) {
            $this->errors = $poll->errors;
            return false;
        }

        return $poll->save(false);
    }

    public function archive(): bool
    {
        $poll = $this->getModel();
        $poll->archived = true;
        if (!$poll->validate()) {
            $this->errors = $poll->errors;
            return false;
        }

        return $poll->save(false);
    }

    public function revive(): bool
    {
        $poll = $this->getModel();
        $poll->archived = false;
        if (!$poll->validate()) {
            $this->errors = $poll->errors;
            return false;
        }

        return $poll->save(false);
    }

    private ?PollAnswerRepositoryInterface $pollAnswerRepository = null;

    public function getAnswerRepository(): PollAnswerRepositoryInterface
    {
        if (null === $this->pollAnswerRepository) {
            $this->pollAnswerRepository = new PollAnswerRepository($this->getId());
        }

        return $this->pollAnswerRepository;
    }

    private ?PollVoteRepositoryInterface $pollVoteRepository = null;

    public function getVoteRepository(): PollVoteRepositoryInterface
    {
        if (null === $this->pollVoteRepository) {
            $this->pollVoteRepository = new PollVoteRepository($this->getId());
        }

        return $this->pollVoteRepository;
    }

    public function hasMemberVoted($memberId): bool
    {
        return $this->getVoteRepository()->hasMemberVoted($memberId);
    }

    public function isSingleChoice(): bool
    {
        return PollChoice::SINGLE === $this->getModel()->choice_id;
    }

    public function vote($memberId, array $answers): bool
    {
        foreach ($answers as $answerId) {
            if (!$this->getAnswerRepository()->isAnswer($answerId)) {
                throw new LogicException('Provided Poll Answer does not belong to the voted Poll!');
            }

            $pollVoteRepository = $this->getVoteRepository();
            if (!$pollVoteRepository->register($memberId, $answerId)) {
                $this->errors = $pollVoteRepository->getErrors();
                return false;
            }
        }

        return true;
    }
}
