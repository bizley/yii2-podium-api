<?php

declare(strict_types=1);

namespace bizley\podium\api\models\poll;

use bizley\podium\api\base\ModelNotFoundException;
use bizley\podium\api\base\PodiumResponse;
use bizley\podium\api\enums\PollChoice;
use bizley\podium\api\events\ModelEvent;
use bizley\podium\api\interfaces\AnswerFormInterface;
use bizley\podium\api\interfaces\MembershipInterface;
use bizley\podium\api\interfaces\ModelFormInterface;
use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\interfaces\PollFormInterface;
use bizley\podium\api\interfaces\RemoverInterface;
use bizley\podium\api\models\ModelFormTrait;
use bizley\podium\api\repos\PollRepo;
use bizley\podium\api\repos\PollVoteRepo;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\Exception;
use yii\di\Instance;

/**
 * Class PostPollForm
 * @package bizley\podium\api\models\poll
 *
 * @property PollAnswerForm[] $pollAnswers
 */
class PollForm extends PollRepo implements PollFormInterface
{
    use ModelFormTrait;

    public const EVENT_BEFORE_CREATING = 'podium.poll.creating.before';
    public const EVENT_AFTER_CREATING = 'podium.poll.creating.after';
    public const EVENT_BEFORE_EDITING = 'podium.poll.editing.before';
    public const EVENT_AFTER_EDITING = 'podium.poll.editing.after';

    /**
     * @var string|array|ModelFormInterface poll answer form handler
     * Component ID, class, configuration array, or instance of ModelFormInterface.
     */
    public $answerFormHandler = \bizley\podium\api\models\poll\PollAnswerForm::class;

    /**
     * @var string|array|RemoverInterface poll answer remover
     * Component ID, class, configuration array, or instance of RemoverInterface.
     */
    public $answerRemoverHandler = \bizley\podium\api\models\poll\PollAnswerRemover::class;

    /**
     * @var array
     */
    public $answers = [];

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

    private $_oldAnswers = [];

    /**
     * @return array
     */
    public function getOldAnswers(): array
    {
        return $this->_oldAnswers;
    }

    /**
     * @param int $id
     * @param string $oldAnswer
     */
    public function addOldAnswer(int $id, string $oldAnswer): void
    {
        $this->_oldAnswers[$id] = $oldAnswer;
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
     */
    public function setAuthor(MembershipInterface $author): void
    {
        $this->author_id = $author->getId();
    }

    /**
     * @param ModelInterface $thread
     */
    public function setThread(ModelInterface $thread): void
    {
        $this->thread_id = $thread->getId();
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
            [['question', 'revealed', 'choice_id', 'expires_at', 'answers', 'content'], 'required'],
            [['question', 'content'], 'string', 'min' => 3],
            [['revealed'], 'boolean'],
            [['choice_id'], 'in', 'range' => PollChoice::keys()],
            [['expires_at'], 'integer'],
            [['answers'], 'each', 'rule' => ['string', 'min' => 3]],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels(): array
    {
        return [
            'content' => Yii::t('podium.label', 'post.content'),
            'revealed' => Yii::t('podium.label', 'poll.revealed'),
            'choice_id' => Yii::t('podium.label', 'poll.choice.type'),
            'question' => Yii::t('podium.label', 'poll.question'),
            'expires_at' => Yii::t('podium.label', 'poll.expires'),
            'answers' => Yii::t('podium.label', 'poll.answers'),
        ];
    }

    /**
     * @return ModelFormInterface
     */
    public function getAnswerForm(): ModelFormInterface
    {
        return new $this->answerFormHandler;
    }

    /**
     * Creates poll answer.
     * @param int $pollId
     * @param string $answer
     * @return PodiumResponse
     */
    public function createAnswer(int $pollId, string $answer): PodiumResponse
    {
        /* @var $pollAnswerForm AnswerFormInterface */
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
        $handler = $this->answerRemoverHandler;

        return $handler::findById($answerId);
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

            return PodiumResponse::success();

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

            return PodiumResponse::success();

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
