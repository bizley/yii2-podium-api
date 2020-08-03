<?php

declare(strict_types=1);

namespace bizley\podium\api\models\poll;

use bizley\podium\api\base\ModelNotFoundException;
use bizley\podium\api\base\PodiumResponse;
use bizley\podium\api\enums\PollChoice;
use bizley\podium\api\events\ModelEvent;
use bizley\podium\api\InsufficientDataException;
use bizley\podium\api\interfaces\AnswerFormInterface;
use bizley\podium\api\interfaces\MembershipInterface;
use bizley\podium\api\interfaces\ModelFormInterface;
use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\interfaces\PollFormInterface;
use bizley\podium\api\interfaces\RemoverInterface;
use bizley\podium\api\repos\PollVoteRepo;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\Exception;
use yii\db\Transaction;
use yii\di\Instance;

use function time;

/**
 * Class PostPollForm
 * @package bizley\podium\api\models\poll
 *
 * @property PollAnswerForm[] $pollAnswers
 */
class PollForm extends Poll implements PollFormInterface
{
    public const EVENT_BEFORE_CREATING = 'podium.poll.creating.before';
    public const EVENT_AFTER_CREATING = 'podium.poll.creating.after';
    public const EVENT_BEFORE_EDITING = 'podium.poll.editing.before';
    public const EVENT_AFTER_EDITING = 'podium.poll.editing.after';

    /**
     * @var string|array|object|ModelFormInterface poll answer form handler
     * Component ID, class, configuration array, or instance of ModelFormInterface.
     */
    public $answerFormHandler = \bizley\podium\api\models\poll\PollAnswerForm::class;

    /**
     * @var string|array|object|RemoverInterface poll answer remover
     * Component ID, class, configuration array, or instance of RemoverInterface.
     */
    public $answerRemoverHandler = \bizley\podium\api\models\poll\PollAnswerRemover::class;

    /**
     * @var array
     */
    public array $answers = [];

    /**
     * @throws InvalidConfigException
     */
    public function init(): void
    {
        parent::init();

        $this->answerFormHandler = Instance::ensure($this->answerFormHandler, ModelFormInterface::class);
        $this->answerRemoverHandler = Instance::ensure($this->answerRemoverHandler, RemoverInterface::class);
    }

    /**
     * @return ActiveQuery
     */
    public function getPollAnswers(): ActiveQuery
    {
        return $this->hasMany(PollAnswerForm::class, ['poll_id' => 'id']);
    }

    private array $oldAnswers = [];

    /**
     * @return array
     */
    public function getOldAnswers(): array
    {
        return $this->oldAnswers;
    }

    /**
     * @param int $id
     * @param string $oldAnswer
     */
    public function addOldAnswer(int $id, string $oldAnswer): void
    {
        $this->oldAnswers[$id] = $oldAnswer;
    }

    public function afterFind(): void
    {
        $answers = $this->pollAnswers;

        foreach ($answers as $pollAnswer) {
            $this->addOldAnswer($pollAnswer->id, $pollAnswer->answer);
            $this->answers[] = $pollAnswer->answer;
        }

        parent::afterFind();
    }

    /**
     * @param MembershipInterface $author
     * @throws InsufficientDataException
     */
    public function setAuthor(MembershipInterface $author): void
    {
        $authorId = $author->getId();
        if ($authorId === null) {
            throw new InsufficientDataException('Missing author Id for poll form');
        }
        $this->author_id = $authorId;
    }

    /**
     * @param ModelInterface $thread
     * @throws InsufficientDataException
     */
    public function setThread(ModelInterface $thread): void
    {
        $threadId = $thread->getId();
        if ($threadId === null) {
            throw new InsufficientDataException('Missing thread Id for poll form');
        }
        $this->thread_id = $threadId;
    }

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
            [['revealed'], 'default', 'value' => true],
            [['choice_id'], 'default', 'value' => PollChoice::SINGLE],
            [['question', 'revealed', 'choice_id', 'expires_at', 'answers'], 'required'],
            [['question'], 'string', 'min' => 3],
            [['revealed'], 'boolean'],
            [['choice_id'], 'in', 'range' => PollChoice::keys()],
            [['expires_at'], 'integer', 'min' => time()],
            [['answers'], 'each', 'rule' => ['string', 'min' => 3]],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels(): array
    {
        return [
            'revealed' => Yii::t('podium.label', 'poll.revealed'),
            'choice_id' => Yii::t('podium.label', 'poll.choice.type'),
            'question' => Yii::t('podium.label', 'poll.question'),
            'expires_at' => Yii::t('podium.label', 'poll.expires'),
            'answers' => Yii::t('podium.label', 'poll.answers'),
        ];
    }

    /**
     * @param array $data
     * @return bool
     */
    public function loadData(array $data = []): bool
    {
        return $this->load($data, '');
    }

    /**
     * @return ModelFormInterface
     */
    public function getAnswerForm(): ModelFormInterface
    {
        return new $this->answerFormHandler();
    }

    /**
     * Creates poll answer.
     * @param int $pollId
     * @param string $answer
     * @return PodiumResponse
     */
    public function createAnswer(int $pollId, string $answer): PodiumResponse
    {
        /** @var AnswerFormInterface $pollAnswerForm */
        $pollAnswerForm = $this->getAnswerForm();

        $pollAnswerForm->setPollId($pollId);
        $pollAnswerForm->setAnswer($answer);

        return $pollAnswerForm->create();
    }

    /**
     * @param int $answerId
     * @return RemoverInterface|null
     */
    public function getAnswerRemover(int $answerId): ?RemoverInterface
    {
        /** @var PollAnswerRemover $handler */
        $handler = $this->answerRemoverHandler;
        /** @var RemoverInterface|null $answerRemover */
        $answerRemover = $handler::findById($answerId);
        return $answerRemover;
    }

    /**
     * Deletes poll answer.
     * @param int $answerId
     * @return PodiumResponse
     * @throws ModelNotFoundException
     */
    public function removeAnswer(int $answerId): PodiumResponse
    {
        $pollAnswerRemover = $this->getAnswerRemover($answerId);

        if ($pollAnswerRemover === null) {
            throw new ModelNotFoundException('Poll of given ID can not be found.');
        }

        return $pollAnswerRemover->remove();
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

        if (!$this->validate()) {
            return PodiumResponse::error($this);
        }

        /** @var Transaction $transaction */
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!$this->save(false)) {
                throw new Exception('Error while creating poll!');
            }

            foreach ($this->answers as $answer) {
                if (!$this->createAnswer($this->id, $answer)->result) {
                    throw new Exception('Error while creating poll answer!');
                }
            }

            $this->afterCreate();
            $transaction->commit();

            return PodiumResponse::success($this->getOldAttributes());
        } catch (Throwable $exc) {
            $transaction->rollBack();
            Yii::error(['Exception while creating poll', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error();
        }
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

        if (PollVoteRepo::find()->where(['poll_id' => $this->id])->exists()) {
            $this->addError('id', Yii::t('podium.error', 'poll.already.voted'));

            return PodiumResponse::error($this);
        }

        if (!$this->validate()) {
            return PodiumResponse::error($this);
        }

        /** @var Transaction $transaction */
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!$this->save(false)) {
                throw new Exception('Error while editing poll!');
            }

            $answersToAdd = array_diff($this->answers, $this->getOldAnswers());
            $answersToRemove = array_diff($this->getOldAnswers(), $this->answers);

            foreach ($answersToAdd as $answer) {
                if (!$this->createAnswer($this->id, $answer)->result) {
                    throw new Exception('Error while creating poll answer!');
                }
            }
            foreach (array_keys($answersToRemove) as $answerId) {
                if (!$this->removeAnswer($answerId)->result) {
                    throw new Exception('Error while removing poll answer!');
                }
            }

            $this->afterEdit();
            $transaction->commit();

            return PodiumResponse::success($this->getOldAttributes());
        } catch (Throwable $exc) {
            $transaction->rollBack();
            Yii::error(['Exception while editing poll', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error();
        }
    }

    public function afterEdit(): void
    {
        $this->trigger(self::EVENT_AFTER_EDITING, new ModelEvent(['model' => $this]));
    }

    /**
     * @param ModelInterface $category
     * @throws NotSupportedException
     */
    public function setCategory(ModelInterface $category): void
    {
        throw new NotSupportedException('Poll category can not be set directly.');
    }

    /**
     * @param ModelInterface $forum
     * @throws NotSupportedException
     */
    public function setForum(ModelInterface $forum): void
    {
        throw new NotSupportedException('Poll forum can not be set directly.');
    }
}
