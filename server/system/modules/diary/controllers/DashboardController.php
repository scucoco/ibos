<?php

class DiaryDashboardController extends DashboardBaseController
{
    public function actionIndex()
    {
        $config = Yii::app()->setting->get("setting/diaryconfig");
        $stampDetails = $config["stampdetails"];
        $stamps = array();

        if (!empty($stampDetails)) {
            $stampidArr = explode(",", trim($stampDetails));

            if (0 < count($stampidArr)) {
                foreach ($stampidArr as $stampidStr) {
                    list($stampId, $score) = explode(":", $stampidStr);
                    $stamps[$score] = intval($stampId);
                }
            }
        }

        $stampIds = Stamp::model()->fetchAllIds();
        $diffStampIds = array_diff($stampIds, $stamps);
        $this->render("index", array("config" => $config, "stamps" => $stamps, "diffStampIds" => $diffStampIds));
    }

    public function actionUpdate()
    {
        if (EnvUtil::submitCheck("formhash")) {
            $fieldArr = array("lockday" => 0, "sharepersonnel" => 0, "sharecomment" => 0, "attention" => 0, "autoreview" => 0, "autoreviewstamp" => 1, "remindcontent" => "", "stampenable" => 0, "pointsystem" => 5, "stampdetails" => "");

            foreach ($_POST as $key => $value) {
                if (in_array($key, array_keys($fieldArr))) {
                    $fieldArr[$key] = $value;
                }
            }

            $stampStr = "";

            if (!empty($fieldArr["stampdetails"])) {
                foreach ($fieldArr["stampdetails"] as $score => $stampId) {
                    $stampId = (empty($stampId) ? 0 : $stampId);
                    $stampStr .= $stampId . ":" . $score . ",";
                }
            }

            $fieldArr["stampdetails"] = rtrim($stampStr, ",");
            $apprise = EnvUtil::getRequest("apprise");

            if (empty($_POST["stampdetails"][$apprise])) {
                $fieldArr["autoreview"] = 0;
            } else {
                $fieldArr["autoreviewstamp"] = $_POST["stampdetails"][$apprise];
            }

            Setting::model()->modify("diaryconfig", array("svalue" => serialize($fieldArr)));
            CacheUtil::update("setting");
            $this->success(Ibos::lang("Update succeed", "message"), $this->createUrl("dashboard/index"));
        }
    }
}
