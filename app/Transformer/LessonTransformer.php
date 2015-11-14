<?php
namespace App\Transformer;

/**
 * Class LessonTransformer
 * @package App\Transformer
 */
class LessonTransformer extends Transformer {

    /**
     * @param $lesson
     * @return array
     */
    public function transform($lesson)
    {
        return [
            'title'=>$lesson['title'],
            'content'=>$lesson['body'],
            'is_free'=>(boolean) $lesson['free']
        ];
    }
}