<?php

use core\Controller;
use models\entity\PredictionMessage;
use models\entity\PredictionCategory;
use core\repository\Repository;

class BiscuitsController extends Controller
{
    public function actionForesee()
    {

        $categories = PredictionCategory::repository()
            ->find()
            ->all()
        ;

        /** @var PredictionCategory $category */
        $category = $categories[1];

        d([
            'BiscuitsController',
//            PredictionCategory::repository()->push((new PredictionCategory())
//                ->setCategoryName('Послание с Венеры')
//            ),
//
//            PredictionCategory::repository()->push((new PredictionCategory())
//                ->setCategoryName('Совет на день')
//            ),

            $category->addPredictionMessageInCategory(
                (new PredictionMessage())
                    ->setMessage('Сегодня избегайте черного цвета')
            ),


            $categories,

//        \core\repository\EntityModel::repository()
        ]);

//        $log = (new \models\entity\PredictionMessageLog());
//
//        $message = (new \models\entity\PredictionMessage())
//            ->addPredictionMessageLog($log)
//        ;

//        d([
//            'BiscuitsController action Foresee',
//            $message,
//        ]);

        $this->view->generate('foresee_biscuits', [

        ]);
    }

    public function actionNew()
    {
        if($foreseeBiscuitsNew = $_POST['foreseeBiscuitsNew']){

            $messages = array_diff(explode("\r\n", $foreseeBiscuitsNew['message']), ['']);

            foreach ($messages as $message){
                PredictionMessage::repository()->push((new PredictionMessage)
                    ->setMessage(trim($message))
                );
            }

            d([
                $messages
            ]);
        }

        $this->view->generate('foresee_biscuits_new', [

        ]);
    }
}
