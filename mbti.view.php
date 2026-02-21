<?php
/**
 * @class  mbtiView
 * @author 
 * @brief  mbti module view class
 */
class mbtiView extends mbti
{
    /**
     * @brief Initialization
     */
    public function init()
    {
        $oModuleInfo = Context::get('module_info');
        if(!$oModuleInfo) {
            $oModuleInfo = new stdClass();
            $oModuleInfo->skin = 'default';
        }

        if(!isset($oModuleInfo->skin) || $oModuleInfo->skin === 'USE_DEFAULT' || !$oModuleInfo->skin) {
            $oModuleInfo->skin = 'default';
        }
        
        $this->module_info = $oModuleInfo;
        Context::set('module_info', $oModuleInfo);

        $this->setTemplatePath($this->module_path . 'skins/' . $this->module_info->skin);
    }

    /**
     * @brief Landing Page
     */
    public function dispMbtiIndex()
    {
        $this->setTemplatePath($this->module_path . 'skins/' . $this->module_info->skin);
        $oMbtiModel = getModel('mbti');
        $config = $oMbtiModel->getConfig();
        Context::set('mbti_config', $config);
        
        $this->setTemplateFile('index');
    }

    /**
     * @brief Test Page
     */
    public function dispMbtiTest()
    {
        $this->setTemplatePath($this->module_path . 'skins/' . $this->module_info->skin);

        // Require login for test
        if(!Context::get('logged_info')) {
            $this->setTemplateFile('login_required');
            return;
        }

        $oMbtiModel = getModel('mbti');
        $questions = $oMbtiModel->getQuestions();
        
        Context::set('questions', $questions);
        Context::set('questions_json', json_encode($questions, JSON_UNESCAPED_UNICODE));
        
        $this->setTemplateFile('test');
    }

    /**
     * @brief Result Page
     */
    public function dispMbtiResult()
    {
        $this->setTemplatePath($this->module_path . 'skins/' . $this->module_info->skin);

        // Require login for result
        if(!Context::get('logged_info')) {
            $this->setTemplateFile('login_required');
            return;
        }

        $result_srl = Context::get('result_srl');
        if(!$result_srl) {
            return $this->dispMbtiIndex();
        }

        $oMbtiModel = getModel('mbti');
        $result = $oMbtiModel->getResult($result_srl);

        if(!$result) {
            return new BaseObject(-1, '결과를 찾을 수 없습니다.');
        }

        Context::set('mbti_result', $result);
        $this->setTemplateFile('result');
    }
}
