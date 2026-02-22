<?php
/**
 * @class  mbtiAdminView
 * @author 
 * @brief  mbti module admin view class
 */
class mbtiAdminView extends mbti
{
    /**
     * @brief Initialization
     */
    public function init()
    {
        // Set template path
        $this->setTemplatePath($this->module_path . 'tpl');
    }

    /**
     * @brief Admin index (Configuration)
     */
    public function dispMbtiAdminIndex()
    {
        $oMbtiModel = getModel('mbti');
        $config = $oMbtiModel->getConfig();
        if(!isset($config->allowed_groups) || !is_array($config->allowed_groups)) $config->allowed_groups = [];
        Context::set('config', $config);

        $oMemberModel = getModel('member');
        $group_list = $oMemberModel->getGroups(0);
        Context::set('group_list', $group_list);

        $this->setTemplateFile('index');
    }
}
