<?php

use core\App;
use models\entity\MiniLink;
use models\repository\MiniLinkRepository;

class MinimizedUrlController extends core\Controller
{
    private $errors = [];

    public function actionIndex()
    {
        $linkLifeTime = \core\App::currentDateTime()
            ->modify('+2 hour')
        ;

        $minimizedLink = $this->newMiniUrl();

        $this->view->generate('main', [
            'defaultLifeTime' => $linkLifeTime
                ->format("Y-m-d\TH:00"),
            'minimizedLink' => $minimizedLink,
            'errors' => $this->errors,
        ]);
    }

    public function actionStatisticsFollowing()
    {
        $miniLinks = (new MiniLinkRepository)->findAll();

        /** @var MiniLink $miniLink */
        foreach ($miniLinks as $miniLink){
            if((new DateTime())->getTimestamp() > $miniLink->getLifeTime()){
                $miniLink->timeIsOver = true;
            }

            $miniLink->makeMinimizedLink();
            $miniLink->makeClickLinks();
        }

        $this->view->generate('statistics_following_to_the_links', [
            'miniLinks' => $miniLinks
        ]);
    }

    private function newMiniUrl(): ? string
    {
        if (!$post = $_POST['MinimizedUrl']) {
            return null;
        }

        if(!App::helper()->checkLink($post['original_link'])){
            $this->errors[] = 'битая ссылка';
            return null;
        }

        $originalLink = addslashes($post['original_link']);

        $linkLifeTime = new DateTime($post['life_time'], new \DateTimeZone(App::config()->getParams('date_time_zone')));

        $newMiniLinkKey = (new MinimizedUrlModel())
            ->makeNewMinimizedKey()
        ;

        $MiniLinkRepository = new MiniLinkRepository();

        /** @var MiniLink $miniLink */
        if(!$miniLink = $MiniLinkRepository->findAll(["original_link = '$originalLink'"])[0]){
            $miniLink = (new MiniLink())
                ->setOriginalLink($originalLink)
                ->setMinimizedLinkKey($newMiniLinkKey)
                ->setLifeTime($linkLifeTime->getTimestamp())
            ;

            $MiniLinkRepository->push($miniLink);
        } else if($linkLifeTime->getTimestamp() > $miniLink->getLifeTime()){
            $MiniLinkRepository->push($miniLink
                ->setOriginalLink($originalLink)
                ->setMinimizedLinkKey($newMiniLinkKey)
                ->setLifeTime($linkLifeTime->getTimestamp())
            );
        }

        return $miniLink->makeMinimizedLink();
    }
}
