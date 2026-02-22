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
        
        $logged_info = Context::get('logged_info');
        if($logged_info) {
            $latest_result = $oMbtiModel->getMemberLatestResult($logged_info->member_srl);
            if($latest_result) {
                Context::set('latest_result', $latest_result);
            }
        }

        $is_allowed = $oMbtiModel->isAllowedGroup($config, $logged_info);
        Context::set('is_allowed', $is_allowed);

        if($config->show_stats !== 'N') {
            $stats = $oMbtiModel->getCommunityStats();
            Context::set('mbti_stats', $stats);
        }

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
        $config = $oMbtiModel->getConfig();

        if(!$oMbtiModel->isAllowedGroup($config, Context::get('logged_info'))) {
            return new BaseObject(-1, '해당 검사를 이용할 권한이 없습니다.');
        }

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
        $oMbtiModel = getModel('mbti');

        if(!$result_srl) {
            $logged_info = Context::get('logged_info');
            $latest_result = $oMbtiModel->getMemberLatestResult($logged_info->member_srl);
            if($latest_result) {
                $result_srl = $latest_result->result_srl;
            } else {
                return $this->dispMbtiIndex();
            }
        }
        $result = $oMbtiModel->getResult($result_srl);

        if(!$result) {
            return new BaseObject(-1, '결과를 찾을 수 없습니다.');
        }

        // Load rich type info
        $types_file = $this->module_path . 'conf/types_info.json';
        if(file_exists($types_file)) {
            $types_data = json_decode(file_get_contents($types_file));
            if(isset($types_data->{$result->mbti_type})) {
                Context::set('type_info', $types_data->{$result->mbti_type});
            }
        }

        Context::set('mbti_result', $result);
        $this->setTemplateFile('result');
    }

    /**
     * @brief User's Test History
     */
    public function dispMbtiHistory()
    {
        $this->setTemplatePath($this->module_path . 'skins/' . $this->module_info->skin);

        // Require login for history
        $logged_info = Context::get('logged_info');
        if(!$logged_info) {
            $this->setTemplateFile('login_required');
            return;
        }

        $args = new stdClass();
        $args->member_srl = $logged_info->member_srl;
        $args->page = Context::get('page') ? Context::get('page') : 1;

        $output = executeQueryArray('mbti.getMemberResults', $args);
        
        $oMbtiModel = getModel('mbti');
        if($output->data) {
            foreach($output->data as $key => $val) {
                $output->data[$key]->mbti_subtype = $oMbtiModel->calculateSubtype($val);
            }
        }
        
        // Pass data and pagination info to template
        Context::set('history_list', $output->data);
        Context::set('page_navigation', $output->page_navigation);

        $this->setTemplateFile('history');
    }
}
