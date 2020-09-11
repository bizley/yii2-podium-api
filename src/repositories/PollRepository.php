<?php

declare(strict_types=1);

namespace bizley\podium\api\repositories;

use bizley\podium\api\ars\PollActiveRecord;
use bizley\podium\api\enums\PollAnswerAction;
use bizley\podium\api\enums\PollChoice;
use bizley\podium\api\interfaces\MemberRepositoryInterface;
use bizley\podium\api\interfaces\PollAnswerRepositoryInterface;
use bizley\podium\api\interfaces\PollRepositoryInterface;
use bizley\podium\api\interfaces\PollVoteRepositoryInterface;
use bizley\podium\api\interfaces\RepositoryInterface;
use bizley\podium\api\interfaces\ThreadRepositoryInterface;
use DomainException;
use Exception;
use LogicException;

use function is_int;
use function is_string;

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
        $threadModel = $this->getModel()->thread;
        $parent = new ThreadRepository();
        $parent->setModel($threadModel);

        return $parent;
    }

    public function isArchived(): bool
    {
        return $this->getModel()->archived;
    }

    public function create(
        MemberRepositoryInterface $author,
        ThreadRepositoryInterface $thread,
        array $data,
        array $answers = []
    ): bool {
        $authorId = $author->getId();
        if (!is_int($authorId)) {
            throw new DomainException('Invalid author ID!');
        }
        $threadId = $thread->getId();
        if (!is_int($threadId)) {
            throw new DomainException('Invalid thread ID!');
        }

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

        $this->setModel($poll);

        $answerRepository = $this->getAnswerRepository();
        foreach ($answers as $answer) {
            if (!$answerRepository->create($answer)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @throws Exception
     */
    public function edit(array $answers = [], array $data = []): bool
    {
        $model = $this->getModel();
        if (!$model->load($data, '')) {
            return false;
        }

        if (!$model->save()) {
            $this->errors = $model->errors;

            return false;
        }

        $answerRepository = $this->getAnswerRepository();
        foreach ($answers as $answer) {
            $id = null;
            if (is_string($answer)) {
                $text = $answer;
                $action = PollAnswerAction::ADD;
            } else {
                $text = $answer[0] ?? null;
                $action = $answer['action'] ?? PollAnswerAction::ADD;
                $id = $answer['id'] ?? null;
            }

            switch ($action) {
                case PollAnswerAction::EDIT:
                    $actionResult = $answerRepository->edit($id, $text);
                    break;

                case PollAnswerAction::REMOVE:
                    $actionResult = $answerRepository->remove($id);
                    break;

                case PollAnswerAction::ADD:
                default:
                    $actionResult = $answerRepository->create($text);
                    break;
            }
            if (!$actionResult) {
                return false;
            }
        }

        return true;
    }

    public function move(ThreadRepositoryInterface $thread): bool
    {
        $threadId = $thread->getId();
        if (!is_int($threadId)) {
            throw new DomainException('Invalid thread ID!');
        }

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
            $this->pollAnswerRepository = new PollAnswerRepository($this);
        }

        return $this->pollAnswerRepository;
    }

    private ?PollVoteRepositoryInterface $pollVoteRepository = null;

    public function getVoteRepository(): PollVoteRepositoryInterface
    {
        if (null === $this->pollVoteRepository) {
            $this->pollVoteRepository = new PollVoteRepository($this);
        }

        return $this->pollVoteRepository;
    }

    public function hasMemberVoted(MemberRepositoryInterface $member): bool
    {
        return $this->getVoteRepository()->hasMemberVoted($member);
    }

    public function isSingleChoice(): bool
    {
        return PollChoice::SINGLE === $this->getModel()->choice_id;
    }

    public function vote(MemberRepositoryInterface $member, array $answers): bool
    {
        foreach ($answers as $answerId) {
            if (!$this->getAnswerRepository()->isAnswer($answerId)) {
                throw new LogicException('Provided Poll Answer does not belong to the voted Poll!');
            }

            $pollVoteRepository = $this->getVoteRepository();
            if (!$pollVoteRepository->register($member, $answerId)) {
                $this->errors = $pollVoteRepository->getErrors();

                return false;
            }
        }

        return true;
    }
}
