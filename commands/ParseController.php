<?php

namespace app\commands;

use app\components\Parser;
use Yii;
use yii\base\InvalidParamException;
use yii\console\Controller;
use \yii\helpers\Console;
use Symfony\Component\Console\Helper\FormatterHelper;
use yii\helpers\FileHelper;
use yii\helpers\Json;

/**
 * Parse app statistics from adjust
 */
class ParseController extends Controller
{
    /**
     * Recieve data from adjust for app(s) and store it in CSV
     *
     * @param string $date Need date to parse. Default now
     * @return integer the exit status
     * @throws Exception on failure.
     */
    public function actionIndex($date = null) {
        if ($date) {
            try {
                $date = Yii::$app->formatter->asDate($date);
            } catch (InvalidParamException $e) {
                Console::stderr(Console::ansiFormat('Error: ', [Console::FG_RED, Console::BOLD]) . $date . ' is not a valid date time value' . PHP_EOL);

                return 1;
            }
        } else {
            $date = Yii::$app->formatter->asDate('now');
        }

        $data = Yii::$app->parser->get($date);
        $result = [];
        foreach ($data['result_parameters']['trackers'] as $tracker) {
            $subResult = [];
            $subResult['name'] = $tracker['name'];
            $subResult['kpis'] = [];
            $kpis = Yii::$app->parser->getByTracker($date, $tracker['token']);
            foreach ($kpis['result_set']['dates'][0]['kpi_values'] as $kpiIndex => $kpi) {
                $subResult['kpis'][$kpis['result_parameters']['kpis'][$kpiIndex]] = (string) $kpi;
            }
            $result[] = $subResult;
        }
        $hnd = fopen(Yii::getAlias('@app') . '/storage/' . Yii::$app->formatter->asDate('now', 'yyyy-MM-dd_HH-mm') . '.json', 'w');
        fwrite($hnd, json_encode($result));
        fclose($hnd);

        Console::stdout(Console::ansiFormat('Success!', [Console::FG_GREEN, Console::BOLD]) . PHP_EOL);

        return 0;
    }

    public function actionError() {
    }
}