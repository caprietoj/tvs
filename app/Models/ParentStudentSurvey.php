<?php

namespace App\Models;

use App\Support\SurveyAnswerEvaluator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class ParentStudentSurvey extends Model
{
    use HasFactory;

    protected $fillable = [
        'period',
        'year',
        'month',
        'timestamp',
        'student_name',
        'student_grade',
        'survey_type',
        'route_number',
        'uses_cafeteria',
        'food_quality',
        'portion_satisfaction',
        'menu_offered',
        'menu_variety',
        'food_temperature',
        'dining_cleanliness',
        'store_service',
        'staff_treatment_cafeteria',
        'positive_aspects_cafeteria',
        'improvement_opportunities_cafeteria',
        'withdrawal_reason_cafeteria',
        'uses_transport',
        'punctuality',
        'vehicle_cleanliness',
        'staff_treatment_transport',
        'communication',
        'positive_aspects_transport',
        'improvement_opportunities_transport',
        'withdrawal_reason_transport',
        'provider',
        'source_file',
        'uploaded_by',
        'uploaded_at'
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'uploaded_at' => 'datetime'
    ];

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public static function getAvailablePeriods()
    {
        return self::select('period')
                  ->distinct()
                  ->orderBy('period', 'desc')
                  ->pluck('period');
    }

    public static function getAvailableGrades()
    {
        return self::select('student_grade')
                  ->distinct()
                  ->orderBy('student_grade')
                  ->pluck('student_grade');
    }

    public static function getAvailableProviders()
    {
        return self::select('provider')
                  ->distinct()
                  ->whereNotNull('provider')
                  ->orderBy('provider')
                  ->pluck('provider');
    }

    public static function getCafeteriaData($period = null, $grade = null)
    {
        $query = self::query();

        if ($period) {
            $query->where('period', $period);
        }

        if ($grade) {
            $query->where('student_grade', $grade);
        }

        return $query->get()
            ->filter(fn ($row) => SurveyAnswerEvaluator::usesService($row, 'cafeteria'))
            ->values();
    }

    public static function getTransportData($period = null, $grade = null)
    {
        $query = self::query();

        if ($period) {
            $query->where('period', $period);
        }

        if ($grade) {
            $query->where('student_grade', $grade);
        }

        return $query->get()
            ->filter(fn ($row) => SurveyAnswerEvaluator::usesService($row, 'transport'))
            ->values();
    }

    public static function calculateCafeteriaMetrics($period = null, $grade = null)
    {
        $data = self::getCafeteriaData($period, $grade);
        $total = $data->count();

        if ($total === 0) {
            return [
                'food_quality' => 0,
                'portion_satisfaction' => 0,
                'menu_offered' => 0,
                'menu_variety' => 0,
                'food_temperature' => 0,
                'dining_cleanliness' => 0,
                'staff_treatment' => 0,
                'total_responses' => 0
            ];
        }

        return [
            'food_quality' => self::calculatePositivePercentage($data, 'food_quality', SurveyAnswerEvaluator::CAFETERIA_RATING_POSITIVE),
            'portion_satisfaction' => self::calculatePositivePercentage($data, 'portion_satisfaction', SurveyAnswerEvaluator::SATISFACTION_POSITIVE),
            'menu_offered' => self::calculatePositivePercentage($data, 'menu_offered', SurveyAnswerEvaluator::CAFETERIA_RATING_POSITIVE),
            'menu_variety' => self::calculatePositivePercentage($data, 'menu_variety', SurveyAnswerEvaluator::YES_POSITIVE),
            'food_temperature' => self::calculatePositivePercentage($data, 'food_temperature', SurveyAnswerEvaluator::YES_POSITIVE),
            'dining_cleanliness' => self::calculatePositivePercentage($data, 'dining_cleanliness', SurveyAnswerEvaluator::CLEANLINESS_POSITIVE),
            'staff_treatment' => self::calculatePositivePercentage($data, 'staff_treatment_cafeteria', SurveyAnswerEvaluator::CAFETERIA_RATING_POSITIVE),
            'total_responses' => $total
        ];
    }

    public static function calculateTransportMetrics($period = null, $grade = null)
    {
        $data = self::getTransportData($period, $grade);
        $total = $data->count();

        if ($total === 0) {
            return [
                'punctuality' => 0,
                'vehicle_cleanliness' => 0,
                'staff_treatment' => 0,
                'communication' => 0,
                'total_responses' => 0
            ];
        }

        return [
            'punctuality' => self::calculatePositivePercentage($data, 'punctuality', SurveyAnswerEvaluator::YES_POSITIVE),
            'vehicle_cleanliness' => self::calculatePositivePercentage($data, 'vehicle_cleanliness', SurveyAnswerEvaluator::YES_POSITIVE),
            'staff_treatment' => self::calculatePositivePercentage($data, 'staff_treatment_transport', SurveyAnswerEvaluator::YES_POSITIVE),
            'communication' => self::calculatePositivePercentage($data, 'communication', SurveyAnswerEvaluator::YES_POSITIVE),
            'total_responses' => $total
        ];
    }

    private static function calculatePositivePercentage($collection, $field, $positiveValues)
    {
        return SurveyAnswerEvaluator::positivePercentage(
            $collection->pluck($field),
            $positiveValues
        );
    }

    public static function getAnalysisByGrade($period = null)
    {
        $query = self::select('student_grade', DB::raw('COUNT(*) as total'))
                    ->groupBy('student_grade');

        if ($period) {
            $query->where('period', $period);
        }

        return $query->get();
    }

    public static function getProviderComparison($period1, $period2)
    {
        $data1 = self::where('period', $period1)->get();
        $data2 = self::where('period', $period2)->get();

        $provider1 = $data1->first()->provider ?? 'Unknown';
        $provider2 = $data2->first()->provider ?? 'Unknown';

        $cafeteria1 = self::calculateCafeteriaMetrics($period1);
        $cafeteria2 = self::calculateCafeteriaMetrics($period2);

        $transport1 = self::calculateTransportMetrics($period1);
        $transport2 = self::calculateTransportMetrics($period2);

        return [
            'period1' => $period1,
            'period2' => $period2,
            'provider1' => $provider1,
            'provider2' => $provider2,
            'cafeteria_comparison' => [
                'period1' => $cafeteria1,
                'period2' => $cafeteria2,
                'differences' => self::calculateDifferences($cafeteria1, $cafeteria2)
            ],
            'transport_comparison' => [
                'period1' => $transport1,
                'period2' => $transport2,
                'differences' => self::calculateDifferences($transport1, $transport2)
            ]
        ];
    }

    private static function calculateDifferences($metrics1, $metrics2)
    {
        $differences = [];
        
        foreach ($metrics1 as $key => $value1) {
            if (isset($metrics2[$key]) && is_numeric($value1) && is_numeric($metrics2[$key])) {
                $difference = $metrics2[$key] - $value1;
                $differences[$key] = [
                    'period1' => $value1,
                    'period2' => $metrics2[$key],
                    'difference' => $difference,
                    'percentage_change' => $value1 > 0 ? round(($difference / $value1) * 100, 1) : 0,
                    'trend' => $difference > 0 ? 'improvement' : ($difference < 0 ? 'decline' : 'stable')
                ];
            }
        }
        
        return $differences;
    }
}
