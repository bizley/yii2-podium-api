<?php

declare(strict_types=1);

namespace bizley\podium\api\repositories;

use bizley\podium\api\ars\MessageActiveRecord;
use bizley\podium\api\enums\MessageSide;
use bizley\podium\api\interfaces\MemberRepositoryInterface;
use bizley\podium\api\interfaces\MessageParticipantRepositoryInterface;
use bizley\podium\api\interfaces\MessageRepositoryInterface;
use LogicException;

final class MessageRepository implements MessageRepositoryInterface
{
    use ActiveRecordRepositoryTrait;

    public string $activeRecordClass = MessageActiveRecord::class;

    private ?MessageActiveRecord $model = null;

    public function getActiveRecordClass(): string
    {
        return $this->activeRecordClass;
    }

    public function getModel(): MessageActiveRecord
    {
        if (null === $this->model) {
            throw new LogicException('You need to call fetchOne() or setModel() first!');
        }

        return $this->model;
    }

    public function setModel(?MessageActiveRecord $activeRecord): void
    {
        $this->model = $activeRecord;
    }

    public function getParent(): MessageRepositoryInterface
    {
        $message = $this->getModel()->replyTo;
        $parent = new self();
        $parent->setModel($message);

        return $parent;
    }

    public function getId(): int
    {
        return $this->getModel()->id;
    }

    public function getParticipant(MemberRepositoryInterface $member): MessageParticipantRepositoryInterface
    {
        $sender = $this->getModel()->sender;
        $receiver = $this->getModel()->receiver;
        $memberId = $member->getId();

        $participant = new MessageParticipantRepository();

        if ($sender && $sender->member_id === $memberId) {
            $participant->setModel($sender);
        } elseif ($receiver && $receiver->member_id === $memberId) {
            $participant->setModel($receiver);
        }

        return $participant;
    }

    public function isCompletelyDeleted(): bool
    {
        $sender = $this->getModel()->sender;
        $receiver = $this->getModel()->receiver;

        return null === $sender && null === $receiver;
    }

    public function send(
        MemberRepositoryInterface $sender,
        MemberRepositoryInterface $receiver,
        MessageRepositoryInterface $replyTo = null,
        array $data = []
    ): bool {
        /** @var MessageActiveRecord $message */
        $message = new $this->activeRecordClass();
        if (!$message->load($data, '')) {
            return false;
        }

        if ($replyTo) {
            if (!$replyTo->isProperReply($sender, $receiver)) {
                return false;
            }
            $message->reply_to_id = $replyTo->getId();
        }

        if (!$message->save()) {
            $this->errors = $message->errors;

            return false;
        }

        $this->setModel($message);

        $messageSender = new MessageParticipantRepository();
        if (!$messageSender->copy($this, $sender, MessageSide::SENDER)) {
            $this->errors = $messageSender->getErrors();

            return false;
        }

        $messageReceiver = new MessageParticipantRepository();
        if (!$messageReceiver->copy($this, $receiver, MessageSide::RECEIVER)) {
            $this->errors = $messageReceiver->getErrors();

            return false;
        }

        return true;
    }

    public function isProperReply(
        MemberRepositoryInterface $replySender,
        MemberRepositoryInterface $replyReceiver
    ): bool {
        $originalSender = $this->getModel()->sender;
        $originalReceiver = $this->getModel()->receiver;

        return $originalSender
            && $originalSender->member_id === $replyReceiver->getId()
            && $originalReceiver
            && $originalReceiver->member_id === $replySender->getId();
    }
}
