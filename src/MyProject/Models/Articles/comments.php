<?php

namespace MyProject\Models\Articles;

use MyProject\Exceptions\InvalidArgumentException;
use MyProject\Models\ActiveRecordEntity;

class Comments extends ActiveRecordEntity
{
    /** @var int */
    protected $id;

    /** @var int */
    protected $userId;

    /** @var int */
    protected $articleId;

    /** @var string */
    protected $text;

    /** @var string */
    protected $createdAt;

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @param string $text
     */
    public function setText(string $text): void
    {
        $this->text = $text;
    }

    //метод для добавления комментариев
    public static function createComment(array $fields, User $author)
    {
        if (empty($fields['text'])) {
            throw new InvalidArgumentException('Добавьте текст комментария');
        }

        $comments = new Comments();
        $comments->setText($fields['text']);

        $comments->save();

        return $comments;
    }

    protected static function getTableName(): string
    {
        return 'articles';
    }
}
