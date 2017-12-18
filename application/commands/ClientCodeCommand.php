<?php

class ClientCodeCommand extends CConsoleCommand
{
    public function actionDeploy()
    {
        $clientCodes = ClientCode::model()->findAll();
        foreach ($clientCodes as $cc) {
            $cc->validateDeployment();
            if($cc->hasErrors()){
                var_dump($cc->platform_id, $cc->getErrors());
            }
        }
    }
}