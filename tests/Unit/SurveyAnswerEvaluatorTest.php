<?php

use App\Support\SurveyAnswerEvaluator;

test('affirmative answers are detected with accents and trailing punctuation', function () {
    expect(SurveyAnswerEvaluator::isAffirmative('Sí'))->toBeTrue()
        ->and(SurveyAnswerEvaluator::isAffirmative('Si.'))->toBeTrue()
        ->and(SurveyAnswerEvaluator::isAffirmative('Si. En este caso, continúe'))->toBeTrue()
        ->and(SurveyAnswerEvaluator::isAffirmative('No. En este caso'))->toBeFalse()
        ->and(SurveyAnswerEvaluator::isAffirmative('Algunas veces'))->toBeFalse();
});

test('positive percentages use only favorable and non-empty answers', function () {
    $answers = collect(['Sí', 'Sí', 'Algunas veces', 'No', '', null]);

    expect(SurveyAnswerEvaluator::positivePercentage($answers, SurveyAnswerEvaluator::YES_POSITIVE))
        ->toBe(50.0);

    $ratings = collect(['Excelente', 'Bueno', 'Regular', 'Malo', 5, 3]);

    expect(SurveyAnswerEvaluator::positivePercentage($ratings, SurveyAnswerEvaluator::CAFETERIA_RATING_POSITIVE))
        ->toBe(50.0);
});
